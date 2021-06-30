<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/posts", name="posts.")
 */
class PostController extends AbstractController
{
    public function __construct(
        private PostRepository $postRepository,
    ) {
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $data = $this->postRepository->findAll();

        return $this->json([
            'page'  => $request->get('page', 1),
            'limit' => $request->get('limit', 10),
            'data'  => $data,
            'links' => array_map(fn (Post $post)
                    => $this->generateUrl('posts.showBySlug', ['slug' => $post->getSlug()]), $data),
        ]);
    }

    /**
     * @Route("/{id}", name="show", requirements={"id": "\d+"})
     */
    public function show(Post $post): Response
    {
        return $this->json($post);
    }

    /**
     * @Route("/{slug}", name="showBySlug")
     * @ParamConverter("post", class="App\Entity\Post", options={"mapping": {"slug": "slug"}})
     */
    public function showBySlug(Post $post): Response
    {
        return $this->json($post);
    }

    /**
     * @Route("/", name="store", methods={"POST"})
     */
    public function store(Request $request): JsonResponse
    {
        $postObject = $this->get('serializer')
            ->deserialize($request->getContent(), Post::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($postObject);
        $em->flush();

        return $this->json($postObject);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     */
    public function delete(Post $post): JsonResponse
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($post);
        $manager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT); // 204
    }
}
