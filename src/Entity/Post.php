<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
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
        "post" => ["security" => "is_granted('ROLE_WRITER')"],
    ],
    itemOperations: [
        "get" => [
            "normalization_context" => ["groups" => ["posts:show"]],
        ],
        "put" => [
            // If the user has role ROLE_EDITOR he can modify post.
            // Or If the user has role ROLE_WRITER and he is the owner of this post, then he can modify it.
            "security" => "is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() == user)",
        ],
    ],
    attributes: [
        "order" => ["createdAt" => "DESC"],
    ],
    denormalizationContext: [
        "groups" => ["posts:fillable"]
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id'      => 'exact',
    'title'   => 'partial',
    'content' => 'partial',
    'author'  => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Post implements AuthoredEntityInterface, HasDatesInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    #[Groups(["post:comments:subresource", "posts:show"])]
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    #[Groups(["posts:show"])]
    private UserInterface $author;

    /**
     * @ORM\Column(type="string", unique=true, columnDefinition="VARCHAR(255) NOT NULL AFTER `id`")
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=255)
     */
    #[Groups(["posts:fillable", "posts:show"])]
    private string $slug;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=10, max=255)
     */
    #[Groups(["posts:fillable", "posts:show"])]
    private string $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=20)
     */
    #[Groups(["posts:fillable", "posts:show"])]
    private string $content;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    #[Groups(["posts:show"])]
    private DateTimeInterface $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     */
    #[ApiSubresource()]
    #[Groups(["posts:show"])]
    private Collection $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image")
     * @ORM\JoinTable(name="posts_images")
     */
    #[ApiSubresource()]
    #[Groups(["posts:fillable", "posts:show"])]
    private Collection $images;

    #[Pure] public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): void
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }
    }

    public function removeImage(Image $image): void
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }
    }
}
