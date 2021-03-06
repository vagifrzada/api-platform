<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Enum\UserRole;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_KEY = 'symf-post-';

    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            $post = new Post();
            $user = $this->getRandomUser();
            $post->setAuthor($user);
            $post->setTitle($faker->realText(50));
            $post->setSlug($faker->slug());
            $post->setContent($faker->realText(150));
            $post->setCreatedAt($faker->dateTimeThisYear());
            $manager->persist($post);

            $this->addReference(self::REFERENCE_KEY . $i, $post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    private function getRandomUser(): User
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::getRandomReferenceKey());
        return !$user->canWritePost() ? $this->getRandomUser() : $user;
    }
}
