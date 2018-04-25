<?php

namespace Lamoda\Metric\MetricBundle\Command;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Storage\StorageRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MaterializeMetricsCommand extends Command
{
    protected static $defaultName = 'metrics:materialize';

    /** @var CollectorRegistry */
    private $collectorRegistry;
    /** @var StorageRegistry */
    private $storageRegistry;

    public function __construct(CollectorRegistry $collectorRegistry, StorageRegistry $storageRegistry)
    {
        parent::__construct();
        $this->collectorRegistry = $collectorRegistry;
        $this->storageRegistry = $storageRegistry;
    }

    protected function configure()
    {
        $this->addArgument('collector', InputArgument::REQUIRED, 'Collector name from configuration');
        $this->addArgument('storage', InputArgument::REQUIRED, 'Storage name from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collector = $this->collectorRegistry->getCollector($input->getArgument('collector'));
        $storage = $this->storageRegistry->getStorage($input->getArgument('storage'));

        $metrics = [];
        foreach ($collector->collect()->getMetrics() as $metric) {
            $metrics[] = new Metric($metric->getName(), $metric->resolve(), $metric->getTags());
        }
        $newSource = new IterableMetricSource($metrics);

        $storage->receive($newSource);
    }
}
