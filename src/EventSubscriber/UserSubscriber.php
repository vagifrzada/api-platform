<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use App\Security\UserConfirmationTokenGenerator;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function hashPassword(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            !$user instanceof User ||
            $request->getMethod() !== Request::METHOD_POST
        ) {
            return;
        }

        // Registering/storing user.
        // This is a user instance. So, we need to hash password.
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $user->getPassword())
        );

        $user->setConfirmationToken(UserConfirmationTokenGenerator::generate());
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['hashPassword', EventPriorities::PRE_WRITE],
        ];
    }
}
