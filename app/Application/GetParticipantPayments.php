<?php

declare(strict_types=1);

namespace App\Application;

final readonly class GetParticipantPayments
{
    public function __construct(
        public string $email,
        public string $untilDate // Date string
    ) {
    }
}
