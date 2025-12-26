<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Price;
use App\Domain\SubscriptionRepository;
use DateTimeImmutable;

final readonly class GetParticipantPaymentsHandler
{
    public function __construct(
        private SubscriptionRepository $repository
    ) {
    }

    /** @return array<string, Price> Key is date, value is sum */
    public function __invoke(GetParticipantPayments $query): array
    {
        $until = new DateTimeImmutable($query->untilDate);
        $subscriptions = $this->repository->findAll();
        
        $sums = [];
        
        foreach ($subscriptions as $subscription) {
            $isParticipant = false;
            foreach ($subscription->getParticipants() as $participant) {
                if ($participant->email === $query->email) {
                    $isParticipant = true;
                    break;
                }
            }
            
            if (!$isParticipant) {
                continue;
            }
            
            $history = $subscription->getPaymentHistory($until);
            foreach ($history as $payment) {
                $dateKey = $payment->dueDate->format('Y-m-d');
                if (!isset($sums[$dateKey])) {
                    $sums[$dateKey] = new Price(0, $payment->pricePerParticipant->currency);
                }
                
                // Assuming same currency for simplicity now, or we could handle multiple
                $sums[$dateKey] = new Price(
                    $sums[$dateKey]->amount + $payment->pricePerParticipant->amount,
                    $sums[$dateKey]->currency
                );
            }
        }
        
        ksort($sums);
        
        return $sums;
    }
}
