<?php

declare(strict_types=1);

namespace App\Quote\Application\Create;

use App\Quote\Quote;
use App\Shared\Domain\ShouldQueue;
use App\Shared\Infrastructure\Validator\Validable;
use Symfony\Component\Validator\Constraints as Assert;

class Command implements Validable, ShouldQueue
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    public string $currencyFrom;
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    public string $currencyTo;
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    public float $rate;
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    public string $date;

    private Quote $result;

    public function getResult(): ?array
    {
        return $this->result?->toArray();
    }

    public function setResult(Quote $result): void
    {
        $this->result = $result;
    }
}
