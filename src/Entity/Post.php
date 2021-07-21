<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use App\Contract\HasDatesInterface;
use App\Contract\AuthoredEntityInterface;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ORM\Table("posts")
 *
 * @UniqueEntity("slug")
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
        "groups" => ["posts:fillable"]
    ],
)]
class Post implements AuthoredEntityInterface, HasDatesInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private UserInterface $author;

    /**
     * @ORM\Column(type="string", unique=true, columnDefinition="VARCHAR(255) NOT NULL AFTER `id`")
     * @Groups({"posts:fillable"})
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=255)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"posts:fillable"})
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"posts:fillable"})
     * @Assert\NotBlank()
     * @Assert\Length(min=20)
     */
    private string $content;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     */
    private Collection $comments;

    #[Pure] public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
        $user->addPost($this);
    }

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }

        return $this;
    }
}
