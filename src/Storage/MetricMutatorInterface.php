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
    public function adjustMetric(string $name, float $delta, array $tags = []): void;

    /**
     * Set metric absolute value.
     *
     * @param string   $name
     * @param float    $value
     * @param string[] $tags
     */
    public function setMetric(string $name, float $value, array $tags = []): void;
}
