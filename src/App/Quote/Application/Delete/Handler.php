<?php

declare(strict_types=1);

namespace App\Quote\Application\Delete;

use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Shared\Domain\Flusher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Handler implements MessageHandlerInterface
{
    public function __construct(
        protected QuoteRepository $quoteRepository,
        protected Flusher $flusher,
        protected LoggerInterface $logger,
    ) {

    }

    /**
     * @throws \Throwable
     */
    public function __invoke(Command $command)
    {
        $this->handler($command);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function handler(Command $command): bool
    {
        $result = $this->quoteRepository->remove($command->id);
        $this->flusher->flush();

        return $result;
    }
}
