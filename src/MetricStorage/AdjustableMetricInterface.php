<?php

namespace Lamoda\MetricStorage;

interface AdjustableMetricInterface
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
