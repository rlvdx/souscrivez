<?php

declare(strict_types=1);

namespace App\Domain;

use DateMalformedStringException;
use DateTimeImmutable;

final class Subscription
{
    /** @var Participants */
    public Participants $participants {
        get {
            return $this->participants;
        }
    }

    public function __construct(
        private readonly SubscriptionId    $id,
        private readonly string            $serviceName,
        private readonly int               $availableSeats,
        private readonly DateTimeImmutable $subscribedOn,
        private readonly Frequency         $frequency,
        private readonly Price             $price
    ) {
        $this->participants = Participants::empty();
    }

    public function getId(): SubscriptionId
    {
        return $this->id;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getAvailableSeats(): int
    {
        return $this->availableSeats;
    }

    public function getSubscribedOn(): DateTimeImmutable
    {
        return $this->subscribedOn;
    }

    public function getFrequency(): Frequency
    {
        return $this->frequency;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function addParticipant(Participant $participant): void
    {
        if (count($this->participants) >= $this->availableSeats) {
            throw new \DomainException('No more seats available');
        }

        foreach ($this->participants as $existing) {
            if ($existing->email === $participant->getEmail()) {
                throw new \DomainException('Participant already added');
            }
        }

        $this->participants->add($participant);
    }

    public function getPricePerParticipant(): Price
    {
        return new Price($this->price->amount / $this->availableSeats, $this->price->currency);
    }

    /**
     * @return Payment[]
     * @throws DateMalformedStringException
     */
    public function getPaymentHistory(DateTimeImmutable $until): array
    {
        $payments = [];
        $current = $this->subscribedOn;
        $pricePerParticipant = $this->getPricePerParticipant();

        while ($current <= $until) {
            $payments[] = new Payment($current, $pricePerParticipant);
            $current = $this->getNextDate($current);
        }

        return $payments;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getNextPaymentDate(DateTimeImmutable $after): DateTimeImmutable
    {
        $current = $this->subscribedOn;
        while ($current <= $after) {
            $current = $this->getNextDate($current);
        }

        return $current;
    }

    /**
     * @throws DateMalformedStringException
     */
    private function getNextDate(DateTimeImmutable $date): DateTimeImmutable
    {
        return match ($this->frequency) {
            Frequency::MONTHLY => $date->modify('+1 month'),
            Frequency::YEARLY => $date->modify('+1 year'),
        };
    }
}
