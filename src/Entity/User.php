<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserRole;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Doctrine\ORM\Mapping as ORM;
use App\Contract\HasDatesInterface;
use App\Entity\Traits\CanResetPassword;
use App\Action\User\ResetPasswordAction;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Table("users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 */
#[ApiResource(
    collectionOperations: [
        "post" => [
            "denormalization_context" => ["groups" => ["users:store"]],
        ],
    ],
    itemOperations: [
        "get" => [
            "security" => "is_granted('IS_AUTHENTICATED_FULLY')",
        ],
        "put" => [
            "security" => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
            "denormalization_context" => ["groups" => ["users:modify"]],
        ],
        "reset-password" => [
            "security" => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
            "method" => "PUT",
            "path" => "/users/{id}/reset-password",
            "controller" => ResetPasswordAction::class,
            "denormalization_context" => ["groups" => "users:reset-password"],
        ],
    ],
    normalizationContext: ["groups" => ["users:read"]]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, HasDatesInterface
{
    use CanResetPassword;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    #[Groups(["users:read", "post:comments:subresource", "posts:show"])]
    private int $id;

    /**
     * @ORM\Column(type="string", unique=true, length=100)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    #[Groups(["users:store", "admin:users:read", "owner:users:read"])]
    private string $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=100)
     */
    #[Groups(["users:read", "users:store", "users:modify", "post:comments:subresource", "posts:show"])]
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"users:store"})
     * @Assert\Regex(
     *     pattern="/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z-_\d]{7,}/",
     *     message="Password must be minimum seven characters long and contain at least one digit and one uppercase and lowercase letter.",
     *     groups={"users:store"}
     * )
     * @Assert\Expression(
     *     expression="this.isValidPassword()",
     *     message="Password must be confirmed",
     *     groups={"users:store"}
     * )
     */
    #[Groups(["users:store"])]
    private string $password;

    /**
     * @Assert\NotBlank(groups={"users:store"})
     */
    #[Groups(["users:store"])]
    private string $passwordConfirmation;

    /**
     * @ORM\Column(type="integer", name="password_changed_at", nullable=true)
     */
    private ?int $passwordChangedAt = null;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $enabled = false;

    /**
     * @ORM\Column(name="confirmation_token", nullable=true, length=70)
     */
    private ?string $confirmationToken = null;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=false)
     */
    #[Groups(["users:read", "users:store", "post:comments:subresource", "posts:show"])]
    private DateTimeInterface $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author")
     */
    #[Groups(["users:read"])]
    private Collection $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     */
    private Collection $comments;

    /**
     * @ORM\Column(type="simple_array", length=200, nullable=true)
     */
    #[Groups(["admin:users:read", "owner:users:read"])]
    private array $roles;

    #[Pure] public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = UserRole::getDefaultRoles();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordConfirmation(): string
    {
        return $this->passwordConfirmation;
    }

    public function setPasswordConfirmation(string $passwordConfirmation): self
    {
        $this->passwordConfirmation = $passwordConfirmation;

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

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    #[Pure] public function isValidPassword(): bool
    {
        return $this->getPassword() === $this->getPasswordConfirmation();
    }

    public function getPasswordChangedAt(): ?int
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?int $time): self
    {
        $this->passwordChangedAt = $time;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $token): self
    {
        $this->confirmationToken = $token;

        return $this;
    }

    #[Pure] public function canWritePost(): bool
    {
        return !empty(array_intersect(UserRole::writerRoles(), $this->getRoles()));
    }

    #[Pure] public function canPostAComment(): bool
    {
        return !empty(array_intersect(UserRole::commentatorRoles(), $this->getRoles()));
    }
}
