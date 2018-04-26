<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Storage\Exception\MetricStorageException;

interface MetricMutatorInterface
{
    /**
     * Adjust given metric.
     *
     * @param string   $name
     * @param float    $delta
     * @param string[] $tags
     *
     * @throws MetricStorageException
     */
    public function adjustMetric(float $delta, string $name, array $tags = []): void;

    /**
     * Set metric absolute value.
     *
     * @param string   $name
     * @param float    $value
     * @param string[] $tags
     */
    public function setMetric(float $value, string $name, array $tags = []): void;
}
