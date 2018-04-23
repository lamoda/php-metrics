<?php

namespace Lamoda\Metric\Common\Source;

use Lamoda\Metric\Common\MetricSourceInterface;

final class MergingMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var MetricSourceInterface[] */
    private $sources;

    public function __construct(MetricSourceInterface ...$sources)
    {
        $this->sources = $sources;
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        foreach ($this->sources as $source) {
            yield from $source;
        }
    }
}
