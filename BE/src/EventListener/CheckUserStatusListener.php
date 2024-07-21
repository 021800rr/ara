<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Entity\User;
use App\Config\UserStatus;

class CheckUserStatusListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user instanceof User && $user->getStatus() !== UserStatus::active->name) {
            $response = new JsonResponse(['message' => 'User status is not active'], 403);
            $event->setResponse($response);
        }
    }
}
