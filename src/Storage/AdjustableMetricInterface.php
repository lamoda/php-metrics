<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Common\MetricInterface;

interface AdjustableMetricInterface extends MetricInterface
{
    /**
     * Updates metric with given delta (positive or negative).
     *
     * Atomic operation, if supported by metric driver
     *
     * @param float $delta
     */
    public function adjust(float $delta);
}
