<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\AddParticipantToSubscription;
use App\Application\CreateSubscription;
use App\Application\GetParticipantPayments;
use App\Domain\Subscription;
use App\Domain\SubscriptionId;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class SubscriptionTest extends FeatureTestCase
{
    public function test_admin_can_see_summed_payments_for_a_participant(): void
    {
        // 1. Create two subscriptions
        $this->bus->dispatch(new CreateSubscription(
            id: 'sub1',
            serviceName: 'Spotify',
            availableSeats: 6,
            subscribedOn: '2025-01-01',
            frequency: 'monthly',
            amount: 18.00 // 3€ per participant if 6 seats
        ));

        $this->bus->dispatch(new CreateSubscription(
            id: 'sub2',
            serviceName: 'Netflix',
            availableSeats: 4,
            subscribedOn: '2025-01-01',
            frequency: 'monthly',
            amount: 12.00 // 3€ per participant if 4 seats
        ));

        // 2. Add same participant to both
        $this->bus->dispatch(new AddParticipantToSubscription(
            subscriptionId: 'sub1',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com'
        ));

        $this->bus->dispatch(new AddParticipantToSubscription(
            subscriptionId: 'sub2',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com'
        ));

        // 3. Query summed payments
        $envelope = $this->bus->dispatch(new GetParticipantPayments(
            email: 'john@example.com',
            untilDate: '2025-02-01'
        ));

        $sums = $envelope->last(HandledStamp::class)->getResult();

        // 2025-01-01: 3€ (Spotify) + 3€ (Netflix) = 6€
        // 2025-02-01: 3€ (Spotify) + 3€ (Netflix) = 6€
        $this->assertCount(2, $sums);
        $this->assertEquals(6.0, $sums['2025-01-01']->amount);
        $this->assertEquals(6.0, $sums['2025-02-01']->amount);
    }
    public function test_admin_can_create_a_subscription_and_add_participants(): void
    {
        $subscriptionId = '759f9c96-6b21-4f1b-a9f4-1234567890ab';

        // 1. Admin creates a subscription
        $this->bus->dispatch(new CreateSubscription(
            id: $subscriptionId,
            serviceName: 'Spotify',
            availableSeats: 6,
            subscribedOn: '2025-01-01',
            frequency: 'monthly',
            amount: 17.99
        ));

        // 2. Admin adds a participant
        $this->bus->dispatch(new AddParticipantToSubscription(
            subscriptionId: $subscriptionId,
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com'
        ));

        // 3. Verify
        $subscription = $this->repository->get(new SubscriptionId($subscriptionId));

        $this->assertNotNull($subscription);
        $this->assertEquals('Spotify', $subscription->getServiceName());
        $this->assertEquals(6, $subscription->getAvailableSeats());
        $this->assertCount(1, $subscription->getParticipants());
        $this->assertEquals('John', $subscription->getParticipants()[0]->firstName);
        $this->assertEquals('john.doe@example.com', $subscription->getParticipants()[0]->email);
    }

    public function test_it_cannot_add_more_participants_than_available_seats(): void
    {
        $subscriptionId = '759f9c96-6b21-4f1b-a9f4-1234567890ac';

        $this->bus->dispatch(new CreateSubscription(
            id: $subscriptionId,
            serviceName: 'Netflix',
            availableSeats: 1,
            subscribedOn: '2025-01-01',
            frequency: 'monthly',
            amount: 13.50
        ));

        $this->bus->dispatch(new AddParticipantToSubscription(
            subscriptionId: $subscriptionId,
            firstName: 'User 1',
            lastName: 'One',
            email: 'user1@example.com'
        ));

        try {
            $this->bus->dispatch(new AddParticipantToSubscription(
                subscriptionId: $subscriptionId,
                firstName: 'User 2',
                lastName: 'Two',
                email: 'user2@example.com'
            ));
        } catch (\Symfony\Component\Messenger\Exception\HandlerFailedException $e) {
            $this->assertInstanceOf(\DomainException::class, $e->getPrevious());
            $this->assertEquals('No more seats available', $e->getPrevious()->getMessage());
            return;
        }

        $this->fail('Expected DomainException was not thrown');
    }
}
