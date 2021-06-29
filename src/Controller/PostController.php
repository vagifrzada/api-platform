<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/posts", name="posts.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/{page}", name="index")
     */
    public function index(Request $request): Response
    {
        $data = $this->getData();
        return $this->json([
            'page'  => $request->get('page', 1),
            'limit' => $request->get('limit', 10),
            'data'  => array_map(function (array $item) {
                return array_merge($item, ['url' => $this->generateUrl('posts.showBySlug', ['slug' => $item['slug']])]);
            }, $data),
        ]);
    }

    /**
     * @Route("/{id}", name="show", requirements={"id": "\d+"})
     */
    public function show(int $id): Response
    {
        $data = $this->getData();

        return $this->json(
            $data[array_search($id, array_column($data, 'id'))]
        );
    }

    /**
     * @Route("/{slug}", name="showBySlug")
     */
    public function showBySlug(string $slug): Response
    {
        $data = $this->getData();

        return $this->json(
            $data[array_search($slug, array_column($data, 'slug'))]
        );
    }

    private function getData(): array
    {
        return [
            [
                'id'    => 1,
                'slug'  => 'this-is-my-first-post',
                'title' => 'This is my first post',
            ],
            [
                'id'    => 2,
                'slug'  => 'this-is-my-second-post',
                'title' => 'This is my second post',
            ],
        ];
    }
}
