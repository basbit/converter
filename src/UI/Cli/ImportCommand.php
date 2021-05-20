<?php

namespace UI\Cli;

use App\Quote\Application\Import\Command as Import;
use App\Quote\Infrastructure\Providers\Coindesk;
use App\Quote\Infrastructure\Providers\Ecb;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportCommand extends Command
{
    protected static $defaultName = 'import';
    protected static $defaultDescription = 'Import quotes (provider: ecb|coinbase)';

    public function __construct(protected MessageBusInterface $bus, protected LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument('provider', InputArgument::OPTIONAL, 'provider type')
            ->addArgument('date', InputArgument::OPTIONAL, 'date');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $input->getArgument('provider');
        $date = new DateTime($input->getArgument('date') ?? '');

        $providers = [Ecb::getName(), Coindesk::getName()];

        if ($provider) {
            $providers = [$provider];
        }

        foreach ($providers as $provider) {
            try {
                $this->bus->dispatch(new Import($provider, $date));

                $this->logger->notice('imported type: ' . $provider);
                $output->writeln('<info>Currency quotes imported: </info>');
                $output->writeln('Type: ' . $provider);
                $output->writeln('Date: ' . $date->format('Y-m-d'));
            } catch (Exception $exception) {
                $this->logger->error('imported type: ' . $provider . ' error: ' . $exception->getMessage());
                $output->writeln('Error: ' . $exception->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

}
