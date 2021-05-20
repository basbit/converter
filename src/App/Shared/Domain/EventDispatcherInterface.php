<?php

declare(strict_types=1);

namespace App\Shared\Domain;

interface EventDispatcherInterface
{
    /**
     * @param array<object> $events
     */
    public function dispatch(array $events): void;
}
