<?php

namespace App\Handler;

use App\Entity\MdlUser;
use App\Entity\User;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    protected CacheItemPoolInterface $authPool;

    /**
     * LogoutHandler constructor.
     */
    public function __construct(CacheItemPoolInterface $authPool)
    {
        $this->authPool = $authPool;
    }

    public function onLogoutSuccess(Request $request): Response
    {
        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $authUser = $token->getUser();
        echo $authUser->getId();
        if (!($authUser instanceof User)) {
            return;
        }

        $authItem = $authUser->getToken();
        if ($authItem instanceof CacheItemInterface) {
            try {
                $this->authPool->deleteItem($authItem->getKey());
            } catch (InvalidArgumentException $exception) {
            }
        }
    }
}
