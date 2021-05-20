<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ReadModel;

class FetchersCollection
{
    private iterable $fetchers;

    public function __construct(iterable $fetchers)
    {
        $this->fetchers = $fetchers;
    }

    public function getFetchers(): iterable
    {
        return $this->fetchers;
    }
}
