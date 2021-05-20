<?php

declare(strict_types=1);

namespace App\Quote\Domain;

interface QuotesProviderSpecification
{
    public static function getName(): string;

    public function getQuotes(): array;
}
