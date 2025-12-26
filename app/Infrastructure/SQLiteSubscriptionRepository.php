<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Frequency;
use App\Domain\Participant;
use App\Domain\Price;
use App\Domain\Subscription;
use App\Domain\SubscriptionId;
use App\Domain\SubscriptionRepository;
use DateTimeImmutable;
use PDO;

final readonly class SQLiteSubscriptionRepository implements SubscriptionRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->createTables();
    }

    private function createTables(): void
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS subscriptions (
                id TEXT PRIMARY KEY,
                service_name TEXT NOT NULL,
                available_seats INTEGER NOT NULL,
                subscribed_on TEXT NOT NULL,
                frequency TEXT NOT NULL,
                price_amount REAL NOT NULL,
                price_currency TEXT NOT NULL
            )
        ');

        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS participants (
                id TEXT PRIMARY KEY,
                subscription_id TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                email TEXT NOT NULL,
                FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE
            )
        ');
    }

    public function save(Subscription $subscription): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO subscriptions (id, service_name, available_seats, subscribed_on, frequency, price_amount, price_currency)
            VALUES (:id, :service_name, :available_seats, :subscribed_on, :frequency, :price_amount, :price_currency)
            ON CONFLICT(id) DO UPDATE SET
                service_name = excluded.service_name,
                available_seats = excluded.available_seats,
                subscribed_on = excluded.subscribed_on,
                frequency = excluded.frequency,
                price_amount = excluded.price_amount,
                price_currency = excluded.price_currency
        ');

        $stmt->execute([
            'id' => $subscription->getId()->toString(),
            'service_name' => $subscription->getServiceName(),
            'available_seats' => $subscription->getAvailableSeats(),
            'subscribed_on' => $subscription->getSubscribedOn()->format('Y-m-d H:i:s'),
            'frequency' => $subscription->getFrequency()->value,
            'price_amount' => $subscription->getPrice()->amount,
            'price_currency' => $subscription->getPrice()->currency,
        ]);

        // Simple sync of participants: delete and re-insert
        $stmt = $this->pdo->prepare('DELETE FROM participants WHERE subscription_id = :subscription_id');
        $stmt->execute(['subscription_id' => $subscription->getId()->toString()]);

        $stmt = $this->pdo->prepare('
            INSERT INTO participants (id, subscription_id, first_name, last_name, email)
            VALUES (:id, :subscription_id, :first_name, :last_name, :email)
        ');

        foreach ($subscription->getParticipants() as $participant) {
            $stmt->execute([
                'id' => $participant->getId(),
                'subscription_id' => $subscription->getId()->toString(),
                'first_name' => $participant->firstName,
                'last_name' => $participant->lastName,
                'email' => $participant->email,
            ]);
        }
    }

    public function get(SubscriptionId $id): ?Subscription
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscriptions WHERE id = :id');
        $stmt->execute(['id' => $id->toString()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $subscription = new Subscription(
            new SubscriptionId($data['id']),
            $data['service_name'],
            (int)$data['available_seats'],
            new DateTimeImmutable($data['subscribed_on']),
            Frequency::from($data['frequency']),
            new Price((float)$data['price_amount'], $data['price_currency'])
        );

        $stmt = $this->pdo->prepare('SELECT * FROM participants WHERE subscription_id = :subscription_id');
        $stmt->execute(['subscription_id' => $id->toString()]);
        $participantsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($participantsData as $pData) {
            $subscription->addParticipant(new Participant(
                $pData['first_name'],
                $pData['last_name'],
                $pData['email'],
                $pData['id']
            ));
        }

        return $subscription;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id FROM subscriptions');
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $subscriptions = [];
        foreach ($ids as $id) {
            $subscriptions[] = $this->get(new SubscriptionId($id));
        }

        return $subscriptions;
    }
}
