<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Storage\Exception\MetricStorageException;

interface MetricAdjusterInterface
{
    /**
     * Adjust given metrics
     *
     * @param string   $key
     * @param float    $delta
     * @param string[] $tags
     *
     * @throws MetricStorageException
     */
    public function adjustMetric(string $key, float $delta, array $tags = []);
}
