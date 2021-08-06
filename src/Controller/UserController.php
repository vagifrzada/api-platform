<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route("/users", name: "users.")]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    #[Route("/confirm/{token}", name: "confirm", methods: ["GET"])]
    public function confirm(string $token): RedirectResponse
    {
        try {
            $this->userService->enable($token);
            return $this->redirectToRoute("home");
        } catch (EntityNotFoundException) {
            throw new NotFoundHttpException("Couldn't enable user.");
        }
    }    
}