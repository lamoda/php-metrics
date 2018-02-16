<?php

namespace Lamoda\MetricStorage;

use Lamoda\MetricStorage\Exception\MetricStorageException;

interface AdjustableMetricStorageInterface
{
    /**
     * Returns adjuster for given metric.
     *
     * @param string $key metric key
     *
     * @return AdjustableMetricInterface
     *
     * @throws MetricStorageException if metric is not found in resolver
     */
    public function getAdjustableMetric(string $key): AdjustableMetricInterface;

    /**
     * Checks if resolver has metrics named by given key.
     *
     * @param string $key metric key
     *
     * @return bool
     */
    public function hasAdjustableMetric(string $key): bool;
}
