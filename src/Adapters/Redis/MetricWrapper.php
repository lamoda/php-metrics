<?php

declare(strict_types=1);

namespace Lamoda\Metric\Adapters\Redis;

use Lamoda\Metric\Storage\MutableMetricInterface;

/** @internal */
final class MetricWrapper implements MutableMetricInterface
{
    /** @var MutatorRedisConnectionInterface */
    private $redisConnection;
    /** @var MutableMetricInterface */
    private $metric;

    public function __construct(MutatorRedisConnectionInterface $redisConnection, MutableMetricInterface $metric)
    {
        $this->redisConnection = $redisConnection;
        $this->metric = $metric;
    }

    /** {@inheritdoc} */
    public function adjust(float $delta): void
    {
        $value = $this->redisConnection->adjustMetric($this->getName(), $delta, $this->getTags());
        $this->metric->setValue($value);
    }

    /** {@inheritdoc} */
    public function setValue(float $value): void
    {
        $this->redisConnection->setMetrics([new MetricDto($this->getName(), $value, $this->getTags())]);
        $this->metric->setValue($value);
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->metric->getName();
    }

    /** {@inheritdoc} */
    public function resolve(): float
    {
        return $this->metric->resolve();
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return $this->metric->getTags();
    }
}
