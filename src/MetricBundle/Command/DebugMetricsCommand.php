<?php

namespace Lamoda\Metric\MetricBundle\Command;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Responder\ResponderMetricSourceRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

final class DebugMetricsCommand extends Command
{
    protected static $defaultName = 'metrics:debug';

    /** @var CollectorRegistry */
    private $registry;

    public function __construct(CollectorRegistry $collectorRegistry)
    {
        parent::__construct();
        $this->registry = $collectorRegistry;
    }

    protected function configure()
    {
        $this->addArgument('collector', InputArgument::REQUIRED, 'Collector name from configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collector = $input->getArgument('collector');
        $io = new SymfonyStyle($input, $output);

        $stopwatch = new Stopwatch(true);

        $source = $this->registry->getCollector($collector);

        $table = new Table($io);
        $table->setHeaders(['name', 'value', 'tags', 'resolution time (ms)', 'resolution memory (Mb)']);

        foreach ($source->collect()->getMetrics() as $metric) {
            $stopwatch->start($metric->getName());

            $profile = $stopwatch->stop($metric->getName());
            $table->addRow(
                [
                    $metric->getName(),
                    $metric->resolve(),
                    $this->formatTags($metric->getTags()),
                    $profile->getDuration(),
                    ($profile->getMemory() / 1024 / 1024),
                ]
            );
        }
        $table->render();
    }

    private function formatTags(array $tags): string
    {
        $parts = array_map(
            function (string $val, string $key) {
                return "$key:$val";
            },
            array_values($tags),
            array_keys($tags)
        );

        return implode(', ', $parts);
    }
}
