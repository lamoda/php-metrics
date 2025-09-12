<?php

declare(strict_types=1);

namespace Lamoda\Metric\Adapters\Redis;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;

abstract class AbstractRedisStorage implements \IteratorAggregate, MetricStorageInterface
{
    /** @var RedisConnectionInterface */
    private $redisConnection;

    public function __construct(RedisConnectionInterface $redisConnection)
    {
        $this->redisConnection = $redisConnection;
    }

    /** {@inheritdoc} */
    final public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    final public function receive(MetricSourceInterface $source): void
    {
        $metrics = [];
        foreach ($source->getMetrics() as $metric) {
            $metrics[] = new MetricDto(
                $metric->getName(),
                $metric->resolve(),
                $metric->getTags()
            );
        }
        $this->redisConnection->setMetrics($metrics);
    }

    /** {@inheritdoc} */
    final public function getMetrics(): \Traversable
    {
        $metricsData = $this->redisConnection->getAllMetrics();
        foreach ($metricsData as $metricDto) {
            yield new MetricWrapper(
                $this->redisConnection,
                $this->doCreateMetric($metricDto->name, $metricDto->value, $metricDto->tags)
            );
        }
    }

    /** {@inheritdoc} */
    final public function findMetric(string $name, array $tags = []): ?MutableMetricInterface
    {
        $value = $this->redisConnection->getMetricValue($name, $tags);
        if ($value === null) {
            return null;
        }

        return new MetricWrapper(
            $this->redisConnection,
            $this->doCreateMetric($name, $value, $tags)
        );
    }

    /** {@inheritdoc} */
    final public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface
    {
        $metric = new MetricWrapper(
            $this->redisConnection,
            $this->doCreateMetric($name, 0, $tags)
        );
        $metric->setValue($value);

        return $metric;
    }

    abstract protected function doCreateMetric(string $name, float $value, array $tags = []): MutableMetricInterface;

    /** {@inheritdoc} */
    final public function setMetricValue(string $name, float $value, array $tags = []): void
    {
        $this->redisConnection->setMetrics([new MetricDto($name, $value, $tags)]);
    }

    /**
     * @param HistogramMetricDto $metricDto
     * @return void
     */
    final public function adjustHistogramMetric(HistogramMetricDto $metricDto): void
    {
        $this->redisConnection->adjustHistogramMetric($metricDto);
    }

    /** {@inheritdoc} */
    final public function adjustMetricValue(string $name, float $value, array $tags = []): float
    {
        return $this->redisConnection->adjustMetric($name, $value, $tags);
    }
}
