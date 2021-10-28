<?php

namespace App\Security;

use App\Entity\User;
use Exception;
use JsonException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class Authenticator extends AbstractAuthenticator
{
    protected CacheItemPoolInterface $authPool;
    protected SerializerInterface $serializer;
    protected int $ttl;

    /**
     * Authenticator constructor.
     */
    public function __construct(
        CacheItemPoolInterface $authPool,
        SerializerInterface $serializer,
        int $tokenLifetime
    ) {
        $this->authPool = $authPool;
        $this->serializer = $serializer;
        $this->ttl = $tokenLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): bool
    {
        $credentials = $this->getCredentials($request);
        if (empty($credentials)) {
            return false;
        }

        return isset($credentials['phone'], $credentials['password']);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        $phone = $credentials['phone'];
        if (empty($phone)) {
            throw new CustomUserMessageAuthenticationException('Field "phone" cant be empty');
        }

        $password = $credentials['password'];
        if (empty($password)) {
            throw new CustomUserMessageAuthenticationException('Field "password" cant be empty');
        }

        return new Passport(
            new UserBadge($phone),
            new PasswordCredentials($password)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): JsonResponse
    {
        $authUser = $token->getUser();
        if (!($authUser instanceof User)) {
            throw new CustomUserMessageAuthenticationException('Cant map system user');
        }

        try {
            do {
                $tokenString = $this->generateRandomToken();
            } while ($this->authPool->hasItem($tokenString));

            $authItem = $this->authPool->getItem($tokenString);
            if (!$authItem->isHit()) {
                $authItem->set($authUser->getUserIdentifier());
                $this->authPool->save($authItem);
            }
        } catch (InvalidArgumentException $exception) {
            throw new CustomUserMessageAuthenticationException('Cant store authentication token', [], 0, $exception);
        }

        $responseData = $this->serializer->serialize(
            [
                'success' => true,
                'session' => [
                    'ttl' => $this->ttl,
                    'token' => $tokenString,
                ],
                'user' => $authUser,
            ],
            'json',
            ['groups' => ['login']]
        );

        return new JsonResponse(
            $responseData,
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'code' => Response::HTTP_UNAUTHORIZED,
                'success' => false,
                'message' => "Authentication failed. {$exception->getMessage()}.",
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function getCredentials(Request $request): array
    {
        if ($this->serializer instanceof Serializer) {
            return $this->serializer->decode($request->getContent(), 'json');
        } else {
            try {
                return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return [];
            }
        }
    }

    protected function generateRandomToken(): string
    {
        try {
            return strtr(base64_encode(random_bytes(32)), '+/', '-_');
        } catch (Exception $exception) {
            throw new RuntimeException('Token creation error.', 0, $exception);
        }
    }
}
