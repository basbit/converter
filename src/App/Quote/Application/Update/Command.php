<?php

declare(strict_types=1);

namespace App\Quote\Application\Update;

use App\Quote\Quote;
use App\Shared\Domain\ShouldQueue;
use App\Shared\Infrastructure\Validator\Validable;
use Symfony\Component\Validator\Constraints as Assert;

class Command implements Validable, ShouldQueue
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    public int $id;
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    public float $rate;

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
