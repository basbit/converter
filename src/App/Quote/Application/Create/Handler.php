<?php

declare(strict_types=1);

namespace App\Quote\Application\Create;

use App\Quote\Currency;
use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Quote\Quote;
use App\Shared\Domain\Flusher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Handler implements MessageHandlerInterface
{
    public function __construct(
        protected Quote $quote,
        protected Currency $currency,
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
        $quote = Quote::create(new \DateTime($command->date),
            $this->currency->fromString($command->currencyFrom),
            $this->currency->fromString($command->currencyTo),
            $command->rate);

        $this->quoteRepository->save($quote);
        $this->flusher->flush($quote);

        $command->setResult($quote);
        return $command;
    }
}
