<?php

namespace Lamoda\Metric\Common\Source;

use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;

final class IterableMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var MetricInterface[]|\Traversable|iterable */
    private $metrics;

    /**
     * @param iterable|MetricInterface[] $metrics
     */
    public function __construct(iterable $metrics)
    {
        if (\is_array($metrics)) {
            $metrics = new \ArrayIterator($metrics);
        }

        $this->metrics = $metrics;
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        return $this->metrics;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }
}
