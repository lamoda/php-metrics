<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Common\MetricInterface;

interface MutableMetricInterface extends MetricInterface
{
    /**
     * Updates metric with given delta (positive or negative).
     *
     * Atomic operation, if supported by metric driver
     *
     * @param float $delta
     */
    public function adjust(float $delta): void;

    /**
     * Sets metric value to given.
     *
     * Atomic operation, if supported by metric driver
     *
     * @param float $value
     */
    public function setValue(float $value): void;
}
