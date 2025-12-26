<?php

declare(strict_types=1);

namespace App\Domain;

use Ramsey\Uuid\Uuid;

final class Participant
{
    private string $id;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        ?string $id = null
    ) {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
