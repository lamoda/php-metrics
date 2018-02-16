<?php

namespace Lamoda\MetricResponder;

interface MetricGroupSourceInterface extends \Traversable
{
    /**
     * Returns metric group iterator.
     *
     * @return MetricGroupInterface[]|\Traversable
     */
    public function all(): \Traversable;
}
