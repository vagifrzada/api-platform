<?php

declare(strict_types=1);

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private const REFERENCE_KEY = 'symf-user-';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $index => $userFixture) {
            $user = new User();
            $user->setName($userFixture['name']);
            $user->setEmail($userFixture['email']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $userFixture['password']));
            $user->setRoles($userFixture['roles']);
            $user->setCreatedAt(new DateTime());

            $this->addReference(self::REFERENCE_KEY . $index, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getRandomReferenceKey(): string
    {
        return self::REFERENCE_KEY . rand(0, 5);
    }

    private function getData(): array
    {
        return [
            [
                'name' => 'Vagif Rufullazada',
                'email' => 'vagif@rufullazada.me',
                'password' => 'Secret123',
                'roles' => [UserRole::SUPERADMIN],
            ],
            [
                'name' => 'Kamran Tagiev',
                'email' => 'kamran@tagiev.com',
                'password' => 'Secret123',
                'roles' => [UserRole::ADMIN],
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@doe.com',
                'password' => 'Secret123',
                'roles' => [UserRole::WRITER],
            ],
            [
                'name' => 'Jack Sparrow',
                'email' => 'jack@sparrow.com',
                'password' => 'Secret123',
                'roles' => [UserRole::WRITER],
            ],
            [
                'name' => 'Han Solo',
                'email' => 'han@solo.com',
                'password' => 'Secret123',
                'roles' => [UserRole::EDITOR],
            ],
            [
                'name' => 'Jedi Knight',
                'email' => 'jedi@knight.com',
                'password' => 'Secret123',
                'roles' => [UserRole::COMMENTATOR],
            ],
        ];
    }
}
