<?php
declare(strict_types=1);

namespace App\Quote\Infrastructure\ReadModel\Quote;

use DateTime;

class QuoteView
{
    public int $id;
    public float $rate;
    public string $currencyFrom;
    public string $currencyTo;
    public DateTime $createdAt;
}
