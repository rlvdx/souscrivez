<?php

declare(strict_types=1);

namespace App\Domain;

enum Frequency: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}
