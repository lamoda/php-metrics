<?php

namespace Lamoda\Metric\Adapters\Redis;

/** @internal */
interface RedisConnectionInterface extends MutatorRedisConnectionInterface
{
    /**
     * @return MetricDto[]
     */
    public function getAllMetrics(): array;

    public function getMetricValue(string $key, array $tags): ?float;
}
