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
        "get",
        "post" => ["security" => "is_granted('IS_AUTHENTICATED_FULLY')"],
    ],
    itemOperations: [
        "get",
        "put" => [
            "security" => "is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() == user",
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
    private UserInterface $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    private Post $post;

    /**
     * @ORM\Column(type="text")
     * @Groups({"comments:fillable"})
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=3000)
     */
    private string $body;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
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
