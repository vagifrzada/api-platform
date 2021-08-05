<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

trait CanResetPassword
{
    /**
     * @Assert\NotBlank(groups={"users:reset-password"})
     * @Assert\Regex(
     *     pattern="/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z-_\d]{7,}/",
     *     message="Password must be minimum seven characters long and contain at least one digit and one uppercase and lowercase letter.",
     *     groups={"users:reset-password"}
     * )
     * @Assert\Expression(
     *     expression="this.isValidNewPassword()",
     *     message="Password must be confirmed",
     *     groups={"users:reset-password"}
     * )
     */
    #[Groups(["users:reset-password"])]
    private ?string $newPassword = null;

    /**
     * @Assert\NotBlank(groups={"users:reset-password"})
     */
    #[Groups(["users:reset-password"])]
    private ?string $newConfirmationPassword = null;

    /**
     * @Assert\NotBlank(groups={"users:reset-password"})
     * @UserPassword(groups={"users:reset-password"})
     */
    #[Groups(["users:reset-password"])]
    private ?string $oldPassword = null;

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function getNewConfirmationPassword(): ?string
    {
        return $this->newConfirmationPassword;
    }

    public function setNewConfirmationPassword(?string $newConfirmationPassword): self
    {
        $this->newConfirmationPassword = $newConfirmationPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    #[Pure] public function isValidNewPassword(): bool
    {
        return $this->getNewPassword() === $this->getNewConfirmationPassword();
    }
}
