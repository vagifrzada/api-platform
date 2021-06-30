<?php

declare(strict_types=1);

namespace App\DataFixtures;

use DateTime;
use App\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $post = new Post();
        $post->setTitle('This is my first post !');
        $post->setSlug('this-is-my-first-post');
        $post->setContent('Some text here ...');
        $post->setCreatedAt(new DateTime());

        $manager->persist($post);

        $post = new Post();
        $post->setTitle('This is my second post !');
        $post->setSlug('this-is-my-second-post');
        $post->setContent('Some text here ...');
        $post->setCreatedAt(new DateTime());

        $manager->persist($post);
        $manager->flush();
    }
}
