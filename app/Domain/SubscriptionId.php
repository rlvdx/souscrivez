<?php

declare(strict_types=1);

namespace App\Domain;

use Ramsey\Uuid\Uuid;

final readonly class SubscriptionId
{
    private string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? Uuid::uuid4()->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(SubscriptionId $other): bool
    {
        return $this->value === $other->value;
    }
}
