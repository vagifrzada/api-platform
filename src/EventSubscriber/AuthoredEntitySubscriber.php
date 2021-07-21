<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use JetBrains\PhpStorm\ArrayShape;
use App\Entity\{User, Post, Comment};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthoredEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function attachAuthorForEntity(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            (!$entity instanceof Post && !$entity instanceof Comment)
            || ($request->getMethod() !== Request::METHOD_POST)) {
            return;
        }

        /** @var UserInterface|User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $entity->setAuthor($user);
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['attachAuthorForEntity', EventPriorities::PRE_WRITE]
        ];
    }
}
