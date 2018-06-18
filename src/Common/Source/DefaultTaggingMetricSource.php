<?php

namespace Lamoda\Metric\Common\Source;

use Lamoda\Metric\Common\DefaultTagsMetric;
use Lamoda\Metric\Common\MetricSourceInterface;

final class DefaultTaggingMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var MetricSourceInterface */
    private $source;
    /** @var string[] */
    private $tags;

    /**
     * ExtraTagsMetricSource constructor.
     *
     * @param MetricSourceInterface $source
     * @param string[]              $tags
     */
    public function __construct(MetricSourceInterface $source, array $tags = [])
    {
        $this->source = $source;
        $this->tags = $tags;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        foreach ($this->source as $metric) {
            yield new DefaultTagsMetric($metric, $this->tags);
        }
    }
}
