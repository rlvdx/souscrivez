<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Frequency;
use App\Domain\Price;
use App\Domain\Subscription;
use App\Domain\SubscriptionId;
use App\Domain\SubscriptionRepository;
use DateTimeImmutable;

final readonly class CreateSubscriptionHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {
    }

    public function __invoke(CreateSubscription $command): void
    {
        $subscription = new Subscription(
            new SubscriptionId($command->id),
            $command->serviceName,
            $command->availableSeats,
            new DateTimeImmutable($command->subscribedOn),
            Frequency::from($command->frequency),
            new Price($command->amount, $command->currency)
        );

        $this->repository->save($subscription);
    }
}
