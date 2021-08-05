<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $tokenManager,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function __invoke(User $data): JsonResponse
    {
        $this->validator->validate($data);

        $data->setPassword(
            $this->userPasswordHasher->hashPassword($data, $data->getNewPassword())
        );

        $data->setPasswordChangedAt(time());

        // Saving changes
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Password changed successfully !',
            'token'   => $this->tokenManager->create($data),
        ]);
    }
}
