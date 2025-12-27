<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Participant;
use App\Domain\SubscriptionId;
use App\Domain\SubscriptionRepository;

final readonly class AddParticipantToSubscriptionHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {}

    public function __invoke(AddParticipantToSubscription $command): void
    {
        $subscription = $this->repository->get(new SubscriptionId($command->subscriptionId));

        if (!$subscription) {
            throw new \InvalidArgumentException('Subscription not found');
        }

        $subscription->addParticipant(new Participant(
            $command->participantName,
            $command->participantEmail,
        ));

        $this->repository->save($subscription);
    }
}
