<?php

declare(strict_types=1);

namespace App\Domain;

interface SubscriptionRepository
{
    public function save(Subscription $subscription): void;
    public function get(SubscriptionId $id): ?Subscription;
    /** @return Subscription[] */
    public function findAll(): array;
}
