<?php

declare(strict_types=1);

namespace App\Application;

final readonly class AddParticipantToSubscription
{
    public function __construct(
        public string $subscriptionId,
        public string $firstName,
        public string $lastName,
        public string $email
    ) {
    }
}
