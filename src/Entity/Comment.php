<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\Table("comments")
 * @ApiResource
 */
class Comment
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
    private User $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    private Post $post;

    /**
     * @ORM\Column(type="text")
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

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setAuthor(User $user): self
    {
        $this->author = $user;
        $user->addComment($this);
        return $this;
    }

    public function getAuthor(): User
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
