<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\AddParticipantToSubscription;
use App\Application\AddParticipantToSubscriptionHandler;
use App\Application\CreateSubscription;
use App\Application\CreateSubscriptionHandler;
use App\Application\GetParticipantPayments;
use App\Application\GetParticipantPaymentsHandler;
use App\Application\GetSubscriptionDetails;
use App\Application\GetSubscriptionDetailsHandler;
use App\Application\GetSubscriptions;
use App\Application\GetSubscriptionsHandler;
use App\Infrastructure\SQLiteSubscriptionRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

abstract class FeatureTestCase extends TestCase
{
    protected MessageBus $bus;
    protected SQLiteSubscriptionRepository $repository;
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->repository = new SQLiteSubscriptionRepository($this->pdo);

        $this->bus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                CreateSubscription::class => [new CreateSubscriptionHandler($this->repository)],
                AddParticipantToSubscription::class => [new AddParticipantToSubscriptionHandler($this->repository)],
                GetSubscriptions::class => [new GetSubscriptionsHandler($this->repository)],
                GetSubscriptionDetails::class => [new GetSubscriptionDetailsHandler($this->repository)],
                GetParticipantPayments::class => [new GetParticipantPaymentsHandler($this->repository)],
            ])),
        ]);
    }
}
