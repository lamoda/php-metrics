<?php

namespace Lamoda\Metric\Storage;

interface MetricMutatorInterface
{
    /**
     * Adjust given metric.
     *
     * Creates empty (0) metric if no metric found
     *
     * @param string   $name
     * @param float    $delta
     * @param string[] $tags
     */
    public function adjustMetricValue(float $delta, string $name, array $tags = []): void;

    /**
     * Set metric absolute value.
     *
     * Creates empty (0) metric if no metric found
     *
     * @param string   $name
     * @param float    $value
     * @param string[] $tags
     */
    public function setMetricValue(float $value, string $name, array $tags = []): void;
}
