<?php

declare(strict_types=1);

namespace Lamoda\Metric\Adapters\Redis;

interface MutatorRedisConnectionInterface
{
    public function adjustMetric(string $key, float $delta, array $tags): float;

    /**
     * @param MetricDto[] $metricsData
     */
    public function setMetrics(array $metricsData): void;
}
