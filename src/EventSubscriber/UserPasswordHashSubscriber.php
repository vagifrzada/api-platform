<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHashSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function hashPassword(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();

        if ($request->getMethod() !== Request::METHOD_POST || !$result instanceof User) {
            return;
        }
        // This is a user instance. So, we need to hash password.
        $result->setPassword(
            $this->userPasswordHasher->hashPassword($result, $result->getPassword())
        );
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['hashPassword', EventPriorities::PRE_WRITE],
        ];
    }
}
