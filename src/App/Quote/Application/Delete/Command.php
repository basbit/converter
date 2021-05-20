<?php

declare(strict_types=1);

namespace App\Quote\Application\Delete;

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

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
