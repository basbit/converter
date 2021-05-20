<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Throwable;

class Flusher
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EventDispatcherInterface $dispatcher,
        protected ManagerRegistry $registry,
        protected LoggerInterface $logger
    ) {

    }

    /**
     * @throws Throwable
     */
    public function flush(AggregateRootInterface ...$roots): void
    {
        try {
            $this->em->flush();
            foreach ($roots as $root) {
                $events = $root->releaseEvents();
                $this->dispatcher->dispatch($events);
            }
        } catch (Throwable $t) {
            $this->logger->error(
                sprintf(
                    "Error while flushing: %s\n%s:%s\n%s",
                    $t->getMessage(),
                    $t->getFile(),
                    $t->getLine(),
                    $t->getTraceAsString()
                )
            );
            // Do not catch: let the outer code to deal with
            throw $t;
        } finally {
            if (!$this->em->isOpen()) {
                $this->logger->error('EM was closed');
            }
        }
    }
}
