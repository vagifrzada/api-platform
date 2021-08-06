<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\UserService;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Security\UserConfirmationTokenGenerator;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
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

        $this->userService->needsPasswordRehash($user);
        $user->setConfirmationToken(UserConfirmationTokenGenerator::generate());
        $this->userService->sendConfirmationEmail($user);
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['hashPassword', EventPriorities::PRE_WRITE],
        ];
    }
}
