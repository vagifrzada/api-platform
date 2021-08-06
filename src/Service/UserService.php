<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function enable(string $token): void
    {
        $params = ["confirmationToken" => $token];

        if (($user = $this->userRepository->findOneBy($params)) === null) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(User::class, $params);
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);

        $this->entityManager->flush();
    }

    public function sendConfirmationEmail(User $user)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('vagif@rufullazada.me', 'Vagif Rufullazada'))
            ->to($user->getEmail())
            ->subject('Confirmation email')
            ->htmlTemplate('emails/user-confirmation.html.twig')
            ->context(['user' => $user]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            dd($e->getMessage(), $e->getDebug());
        }
    }

    public function needsPasswordRehash(User $user): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $user->getPassword())
        );
    }
}
