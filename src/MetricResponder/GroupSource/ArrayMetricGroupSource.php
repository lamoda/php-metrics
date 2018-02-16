<?php

namespace Lamoda\MetricResponder\GroupSource;

use Lamoda\MetricResponder\MetricGroupSourceInterface;

final class ArrayMetricGroupSource extends \ArrayIterator implements MetricGroupSourceInterface
{
    /** {@inheritdoc} */
    public function all(): \Traversable
    {
        return $this;
    }
}
