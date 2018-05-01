<?php

namespace Lamoda\Metric\MetricBundle\Command;

use Lamoda\Metric\Storage\Exception\ReceiverException;
use Lamoda\Metric\Storage\MaterializeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MaterializeMetricsCommand extends Command
{
    protected static $defaultName = 'metrics:materialize';

    /**
     * @var MaterializeHelper
     */
    private $helper;

    public function __construct(MaterializeHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    protected function configure(): void
    {
        $this->addArgument('collector', InputArgument::REQUIRED, 'Collector name from configuration');
        $this->addArgument('storage', InputArgument::REQUIRED, 'Storage name from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io = new SymfonyStyle($input, $output);

        $collector = $input->getArgument('collector');
        $storage = $input->getArgument('storage');

        try {
            $this->helper->materialize($collector, $storage);

            return 0;
        } catch (\OutOfBoundsException $exception) {
            $io->error('Invalid argument supplied');
        } catch (ReceiverException $exception) {
            $io->error('Failed to receive metrics in storage: ' . $exception->getMessage());
        }

        return -1;
    }
}
