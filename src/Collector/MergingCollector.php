<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\MergingMetricSource;

final class MergingCollector implements MetricCollectorInterface
{
    /** @var MetricCollectorInterface[] */
    private $collectors;

    public function __construct(array $collectors)
    {
        $this->collectors = $collectors;
    }

    /** {@inheritdoc} */
    public function collect(): MetricSourceInterface
    {
        $sources = [];
        foreach ($this->collectors as $collector) {
            $sources[] = $collector->collect();
        }

        return new MergingMetricSource(...$sources);
    }
}
