<?php

declare(strict_types=1);

namespace App\Application;

final readonly class GetSubscriptionDetails
{
    public function __construct(public string $id)
    {
    }
}
