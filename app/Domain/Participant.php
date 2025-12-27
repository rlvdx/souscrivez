<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Participant
{
    public function __construct(
        private string $name,
        private string $email,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
