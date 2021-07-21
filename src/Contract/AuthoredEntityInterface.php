<?php

declare(strict_types=1);

namespace App\Contract;

use Symfony\Component\Security\Core\User\UserInterface;

interface AuthoredEntityInterface
{
    public function setAuthor(UserInterface $user): void;

    public function getAuthor(): UserInterface;
}
