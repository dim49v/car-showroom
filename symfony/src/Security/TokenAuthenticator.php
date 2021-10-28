<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use function Symfony\Component\String\u;

class TokenAuthenticator extends AbstractAuthenticator
{
    protected const TOKEN_TYPE = 'Bearer';
    protected const GUEST_IDENTIFIER = '';

    protected CacheItemPoolInterface $authPool;
    protected UserRepository $userRepository;
    protected int $ttl;

    /**
     * TokenAuthenticator constructor.
     */
    public function __construct(
        CacheItemPoolInterface $authPool,
        UserRepository $userRepository,
        int $tokenLifetime,
    ) {
        $this->authPool = $authPool;
        $this->userRepository = $userRepository;
        $this->ttl = $tokenLifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        $token = $credentials['token'];
        if (empty($token)) {
            throw new CustomUserMessageAuthenticationException('Token cant be empty');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $token,
                function (string $token): ?UserInterface {
                    return $this->getUserByToken($token);
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $authUser = $token->getUser();
        if (!($authUser instanceof User)) {
            return null;
        }

        $authItem = $authUser->getToken();
        if (($authItem instanceof CacheItemInterface)
            && $authItem->isHit()) {
            $authItem->expiresAfter($this->ttl);
            $this->authPool->save($authItem);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
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
        if ($request->headers->has('Authorization')) {
            $apiToken = u($request->headers->get('Authorization'));
            if (!$apiToken->isEmpty() && $apiToken->startsWith(self::TOKEN_TYPE.' ')) {
                return [
                    'token' => $apiToken->trimStart(self::TOKEN_TYPE.' ')->toString(),
                ];
            }
        }

        return [
            'token' => 'guest',
        ];
    }

    protected function getUserByToken(string $token): ?UserInterface
    {
        try {
            $authItem = $this->authPool->getItem($token);
        } catch (InvalidArgumentException $exception) {
            throw new CustomUserMessageAuthenticationException('Token for user not found', [], 0, $exception);
        }

        if (!$authItem->isHit()) {
            return match ($token) {
                'guest' => $this->userRepository->findOneByIdentifier(self::GUEST_IDENTIFIER),
                default => null,
            };
        } else {
            try {
                $userData = $authItem->get();
            } catch (Exception $exception) {
                throw new CustomUserMessageAuthenticationException('Token for user not found', [], 0, $exception);
            }

            $user = $this->userRepository->findOneByIdentifier($userData);
        }

        if (null === $user) {
            return null;
        }

        return $user->setToken($authItem);
    }
}
