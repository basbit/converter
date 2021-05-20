<?php

declare(strict_types=1);

namespace App\Quote\Application\Import;

use App\Quote\Infrastructure\Providers\Coindesk;
use App\Quote\Infrastructure\Providers\Ecb;
use App\Quote\Infrastructure\Repository\QuoteRepository;
use App\Quote\Quote;
use App\Shared\Domain\Flusher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Handler implements MessageHandlerInterface
{
    public function __construct(
        protected Quote $quote,
        protected QuoteRepository $quoteRepository,
        protected Flusher $flusher,
        protected LoggerInterface $logger,
        protected Coindesk $coindesk,
        protected Ecb $ecb,
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
    private function handler(Command $command): void
    {
        switch ($command->provider) {
            case Ecb::getName():
                $quotes = $this->ecb->getQuotes();
                break;
            case Coindesk::getName():
                $quotes = $this->coindesk->getQuotes();
                break;
        }

        if (isset($quotes)) {
            foreach ($quotes as $quote) {
                $this->quoteRepository->save($quote);
                $this->flusher->flush($quote);
            }
        }
    }
}
