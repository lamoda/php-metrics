<?php

namespace Lamoda\Metric\Common\Factory;

use Lamoda\Metric\Common\MetricInterface;

interface MetricFactoryInterface
{
    /**
     * Create metric instance by containment.
     *
     * @param string   $name
     * @param float    $value
     * @param string[] $tags
     *
     * @return MetricInterface
     */
    public function createMetric(string $name, float $value, array $tags = []): MetricInterface;
}
