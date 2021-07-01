<?php

declare(strict_types=1);

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const REFERENCE_KEY = 'symf-user';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName("Vagif Rufullazada");
        $user->setEmail("vagif@rufullazada.me");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'secret'));
        $user->setCreatedAt(new DateTime());

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::REFERENCE_KEY, $user);
    }
}
