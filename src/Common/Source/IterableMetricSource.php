<?php

namespace Lamoda\Metric\Common\Source;

use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;

final class IterableMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var MetricInterface[]|\Traversable */
    private $metrics;

    /**
     * @param iterable|MetricInterface[]|\Traversable $metrics
     */
    public function __construct($metrics)
    {
        if (!$metrics instanceof \Traversable) {
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
