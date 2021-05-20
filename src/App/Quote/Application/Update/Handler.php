<?php

declare(strict_types=1);

namespace App\Quote\Application\Update;

use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Quote\Quote;
use App\Shared\Domain\Flusher;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Handler implements MessageHandlerInterface
{
    public function __construct(
        protected Quote $quote,
        protected QuoteRepository $quoteRepository,
        protected Flusher $flusher,
        protected LoggerInterface $logger
    ) {

    }

    /**
     * @throws \Throwable
     */
    public function __invoke(Command $command): Command
    {
        $quote = $this->quoteRepository->find($command->id);

        if (!$quote instanceof Quote) {
            throw new Exception('Can`t find quote');
        }

        $quote?->setRate($command->rate);
        $this->quoteRepository->save($quote);
        $this->flusher->flush($quote);

        $command->setResult($quote);
        return $command;
    }
}
