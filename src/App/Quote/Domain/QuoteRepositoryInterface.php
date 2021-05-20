<?php

declare(strict_types=1);

namespace App\Quote\Domain;

use App\Quote\Currency;
use App\Quote\Quote;
use DateTime;

interface QuoteRepositoryInterface
{
    public function get(Currency $currencyFrom, Currency $currencyTo): Quote;

    public function findQuote(string $currencyFrom, string $currencyTo, DateTime $dateTime): Quote;

    public function store(Quote $quote): void;
}
