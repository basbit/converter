<?php

declare(strict_types=1);

namespace App\Quote\Application\Converter;

use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Quote\Quote;
use App\Shared\Domain\Flusher;
use DateTime;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;

class Handler implements MessageHandlerInterface
{
    public function __construct(
        protected Quote $quote,
        protected QuoteRepository $quoteRepository,
        protected Flusher $flusher
    ) {

    }

    /**
     * @throws Throwable
     */
    public function __invoke(Command $command): Command
    {
        $quote = $this->quoteRepository->findQuote($command->from, $command->to, $command->dateTime ?? new DateTime());

        if (!$quote) {
            throw new Exception('Can`t find exchange rate');
        }

        $command->setRate($quote?->getRate());
        $command->setResult($command->amount * $quote?->getRate());
        return $command;
    }
}
