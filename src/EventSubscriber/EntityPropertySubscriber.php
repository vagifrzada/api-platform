<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use DateTime;
use JetBrains\PhpStorm\ArrayShape;
use App\Contract\HasDatesInterface;
use App\Contract\AuthoredEntityInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntityPropertySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function applyPropertyValue(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$this->isCreating($request)) {
            return;
        }

        if ($entity instanceof AuthoredEntityInterface) {
            $entity->setAuthor($this->tokenStorage->getToken()->getUser());
        }

        if ($entity instanceof HasDatesInterface) {
            $entity->setCreatedAt(new DateTime());
        }
    }

    private function isCreating(Request $request): bool
    {
        return $request->getMethod() === Request::METHOD_POST;
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['applyPropertyValue', EventPriorities::PRE_WRITE],
        ];
    }
}
