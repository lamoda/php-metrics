<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\DefaultTaggingMetricSource;
use Lamoda\Metric\Common\Source\MergingMetricSource;

final class MergingCollector implements MetricCollectorInterface
{
    /** @var MetricCollectorInterface[] */
    private $collectors;
    /** @var string[] */
    private $tags;

    /**
     * MergingCollector constructor.
     *
     * @param string[]                   $tags
     * @param MetricCollectorInterface[] $collectors
     */
    public function __construct(array $collectors, array $tags)
    {
        $this->tags = $tags;
        $this->collectors = $collectors;
    }

    /** {@inheritdoc} */
    public function collect(): MetricSourceInterface
    {
        $sources = [];
        foreach ($this->collectors as $collector) {
            $sources[] = new DefaultTaggingMetricSource($collector->collect(), $this->tags);
        }

        return new MergingMetricSource(...$sources);
    }
}
