<?php

declare(strict_types=1);

namespace App\Contract;

use DateTimeInterface;

interface HasDatesInterface
{
    public function setCreatedAt(DateTimeInterface $dateTime): void;

    public function getCreatedAt(): DateTimeInterface;
}
