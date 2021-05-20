<?php

declare(strict_types=1);

namespace App\Quote\Domain;

use App\Quote\Currency;

interface CurrencyRepositoryInterface
{
    public function get(string $currency): ?Currency;
}
