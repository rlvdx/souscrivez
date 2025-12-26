<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Subscription;
use App\Domain\SubscriptionId;
use App\Domain\SubscriptionRepository;

final readonly class GetSubscriptionDetailsHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {
    }

    public function __invoke(GetSubscriptionDetails $query): ?Subscription
    {
        return $this->repository->get(new SubscriptionId($query->id));
    }
}
