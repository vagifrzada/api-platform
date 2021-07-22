<?php

declare(strict_types=1);

namespace App\DataFixtures;

use DateTime;
use Exception;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; $i++) {
            /** @var Post $post */
            $post = $this->getReference(PostFixtures::REFERENCE_KEY . $i);
            for ($j = 0; $j < random_int(1, 15); $j++) {
                $comment = new Comment();
                /** @var User $user */
                $user = $this->getRandomUser();
                $comment->setAuthor($user);
                $comment->setPost($post);
                $comment->setBody($faker->realTextBetween(20, 50));
                $comment->setCreatedAt($faker->dateTimeThisYear());
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }

    private function getRandomUser(): User
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::getRandomReferenceKey());
        return !$user->canPostAComment() ? $this->getRandomUser() : $user;
    }
}
