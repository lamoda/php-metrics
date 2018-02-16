<?php

namespace Lamoda\MetricResponder;

interface MetricSourceInterface extends \Traversable
{
    /**
     * Returns iterable metric set.
     *
     * @return \Traversable|MetricInterface[]
     */
    public function getMetrics(): \Traversable;
}
