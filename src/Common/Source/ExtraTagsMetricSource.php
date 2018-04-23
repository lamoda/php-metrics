<?php

namespace Lamoda\Metric\Common\Source;

use Lamoda\Metric\Common\ExtraTagsMetric;
use Lamoda\Metric\Common\MetricSourceInterface;

final class ExtraTagsMetricSource implements \IteratorAggregate, MetricSourceInterface
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
    public function __construct(MetricSourceInterface $source, array $tags)
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
        foreach ($this->source->getMetrics() as $metric) {
            yield new ExtraTagsMetric($metric, $this->tags);
        }
    }
}
