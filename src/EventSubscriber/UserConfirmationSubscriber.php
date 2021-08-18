<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\UserConfirmation;
use App\Exception\InvalidConfirmationTokenException;
use App\Service\UserService;
use JetBrains\PhpStorm\ArrayShape;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserConfirmationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private LoggerInterface $userConfirmationLogger,
    ) {
    }

    /**
     * @throws InvalidConfirmationTokenException
     */
    public function confirm(ViewEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isConfirmationRequest($request)) {
            return;
        }

        $this->userConfirmationLogger->info("Trying to confirm user...");

        /** @var UserConfirmation $userConfirmation */
        $userConfirmation = $event->getControllerResult();

        try {
            $this->userService->enable($userConfirmation->getToken());
            $this->userConfirmationLogger->info("User enabled successfully !");
        } catch (EntityNotFoundException) {
            $this->userConfirmationLogger->error("Confirmation token not found !");
            throw InvalidConfirmationTokenException::fromMessage("Invalid confirmation token !");
        }

        $event->setResponse(new JsonResponse(null, Response::HTTP_OK));
    }

    #[ArrayShape([KernelEvents::VIEW => "array"])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['confirm', EventPriorities::POST_VALIDATE],
        ];
    }

    private function isConfirmationRequest(Request $request): bool
    {
        return $request->get('_route') === 'api_user_confirmations_post_collection';
    }
}
