<?php

namespace Lamoda\MetricResponder\MetricSource;

use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\MetricSourceInterface;

final class CompositeMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var MetricInterface[]|\Traversable */
    private $metrics;

    /**
     * @param MetricInterface[]|\Traversable $metrics
     */
    public function __construct($metrics)
    {
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
