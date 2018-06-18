<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Storage\Exception\ReceiverException;

final class MaterializeHelper
{
    /** @var CollectorRegistry */
    private $collectorRegistry;
    /** @var StorageRegistry */
    private $storageRegistry;

    /**
     * @param CollectorRegistry $collectorRegistry
     * @param StorageRegistry   $storageRegistry
     */
    public function __construct(CollectorRegistry $collectorRegistry, StorageRegistry $storageRegistry)
    {
        $this->collectorRegistry = $collectorRegistry;
        $this->storageRegistry = $storageRegistry;
    }

    /**
     * Receives metrics from named collector to named storage.
     *
     * @param string $collectorName
     * @param string $storageName
     *
     * @throws ReceiverException
     * @throws \OutOfBoundsException
     */
    public function materialize(string $collectorName, string $storageName): void
    {
        $collector = $this->collectorRegistry->getCollector($collectorName);
        $storage = $this->storageRegistry->getStorage($storageName);

        $storage->receive($this->materializeSource($collector->collect()));
    }

    private function materializeSource(MetricSourceInterface $source): MetricSourceInterface
    {
        $metrics = [];
        foreach ($source->getMetrics() as $metric) {
            $metrics[] = new Metric($metric->getName(), $metric->resolve(), $metric->getTags());
        }

        return new IterableMetricSource($metrics);
    }
}
