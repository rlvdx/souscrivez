<?php

declare(strict_types=1);

namespace App\Application;

final readonly class AddParticipantToSubscription
{
    public function __construct(
        public string $subscriptionId,
        public string $participantName,
        public string $participantEmail
    ) {}
}
