<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Price
{
    public function __construct(
        public float $amount,
        public string $currency = 'EUR'
    ) {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
    }
}
