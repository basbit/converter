<?php

declare(strict_types=1);

namespace App\Quote\Application\Import;

use App\Quote\Quote;
use App\Shared\Domain\ShouldQueue;
use App\Shared\Infrastructure\Validator\Validable;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class Command implements Validable, ShouldQueue
{
    /**
     * @Assert\Blank()
     * @Assert\Type(type="string")
     */
    public string $provider;
    /**
     * @Assert\Blank()
     * @Assert\Type(type="DateTime")
     */
    public DateTime $dateTime;

    public function __construct(string $provider, DateTime $dateTime)
    {
        $this->provider = $provider;
        $this->dateTime = $dateTime;
    }
}
