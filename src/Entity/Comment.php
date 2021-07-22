<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\HasDatesInterface;
use App\Contract\AuthoredEntityInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\Table("comments")
 */
#[ApiResource(
    collectionOperations: [
        "post" => ["security" => "is_granted('ROLE_COMMENTATOR')"],
    ],
    itemOperations: [
        "get",
        "put" => [
            "security" => "is_granted('ROLE_EDITOR') or (is_granted('ROLE_COMMENTATOR') and object.getAuthor() == user)",
        ],
    ],
    subresourceOperations: [
        'api_posts_comments_get_subresource' => [
            'method' => 'GET',
            'normalization_context' => [
                'groups' => ['post:comments:subresource'],
            ],
        ],
    ],
    denormalizationContext: [
        "groups" => ["comments:fillable"],
    ],
)]
class Comment implements AuthoredEntityInterface, HasDatesInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    #[Groups(["post:comments:subresource", "posts:show"])]
    private UserInterface $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    #[Groups(["comments:fillable", "post:comments:subresource"])]
    private Post $post;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=3000)
     */
    #[Groups(["comments:fillable", "post:comments:subresource", "posts:show"])]
    private string $body;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    #[Groups(["post:comments:subresource"])]
    private DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $dateTime): void
    {
        $this->createdAt = $dateTime;
    }

    public function setAuthor(UserInterface $user): void
    {
        /** @var User $user */
        $this->author = $user;
        $user->addComment($this);
    }

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function setPost(Post $post): self
    {
        $this->post = $post;
        $post->addComment($this);
        return $this;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}
