<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Frequency;

final readonly class CreateSubscription
{
    public function __construct(
        public string $id,
        public string $serviceName,
        public int $availableSeats,
        public string $subscribedOn, // Date string
        public string $frequency,
        public float $amount,
        public string $currency = 'EUR'
    ) {
    }
}
