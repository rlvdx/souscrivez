<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\SubscriptionRepository;

final readonly class GetSubscriptionsHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {
    }

    public function __invoke(GetSubscriptions $query): array
    {
        return $this->repository->findAll();
    }
}
