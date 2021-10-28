<?php

namespace App\EventListener;

use App\Entity\User;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    protected CacheItemPoolInterface $authPool;

    public function __construct(CacheItemPoolInterface $authPool)
    {
        $this->authPool = $authPool;
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $logoutEvent): void
    {
        $authUser = $logoutEvent->getToken()?->getUser();
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
        $logoutEvent->setResponse(new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        ));
    }
}