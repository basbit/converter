<?php

declare(strict_types=1);

namespace App\Quote\Application\Converter;

use App\Shared\Domain\ShouldQueue;
use App\Shared\Infrastructure\Validator\Validable;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class Command implements Validable, ShouldQueue
{
    /**
     * @Assert\Type(type="string")
     */
    public string $from;

    /**
     * @Assert\Type(type="string")
     */
    public string $to;

    /**
     * @Assert\Type(type="float")
     */
    public float $amount;

    /**
     * @Assert\Blank()
     * @Assert\Type(type="string")
     */
    public ?DateTime $dateTime = null;

    private float $rate;
    private float $result;

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate(float $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return float
     */
    public function getResult(): float
    {
        return $this->result;
    }

    /**
     * @param float $result
     */
    public function setResult(float $result): void
    {
        $this->result = $result;
    }
}
