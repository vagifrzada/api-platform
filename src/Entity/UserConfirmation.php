<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: [
        "post" => [
            "path"   => "/users/confirm",
            "method" => "POST",
        ],
    ],
    itemOperations: [],
)]
class UserConfirmation
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=64)
     */
    private string $token;

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
