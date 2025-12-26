<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final readonly class Payment
{
    public function __construct(
        public DateTimeImmutable $dueDate,
        public Price $pricePerParticipant
    ) {
    }
}
