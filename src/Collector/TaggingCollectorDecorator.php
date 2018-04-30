<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\DefaultTaggingMetricSource;

final class TaggingCollectorDecorator implements MetricCollectorInterface
{
    /** @var MetricCollectorInterface */
    private $collector;
    /** @var string[] */
    private $tags;

    /**
     * @param MetricCollectorInterface $collector
     * @param string[]                 $tags
     */
    public function __construct(MetricCollectorInterface $collector, array $tags)
    {
        $this->collector = $collector;
        $this->tags = $tags;
    }

    /** {@inheritdoc} */
    public function collect(): MetricSourceInterface
    {
        return new DefaultTaggingMetricSource($this->collector->collect(), $this->tags);
    }
}
