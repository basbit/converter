<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Domain\EventDispatcherInterface as DomainEventDispatcherInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DomainEventDispatcher implements DomainEventDispatcherInterface
{
    private EventDispatcherInterface $bus;

    public function __construct(EventDispatcherInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->bus->dispatch($event);
        }
    }
}
