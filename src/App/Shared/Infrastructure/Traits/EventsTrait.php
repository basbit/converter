<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Traits;

trait EventsTrait
{
    /** @var array<object> */
    private array $recordedEvents = [];

    protected function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
