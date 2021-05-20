<?php

declare(strict_types=1);

namespace App\Shared\Domain;

interface AggregateRootInterface
{
    /**
     * @return array<object>
     */
    public function releaseEvents(): array;
}
