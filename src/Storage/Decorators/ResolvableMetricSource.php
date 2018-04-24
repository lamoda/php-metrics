<?php

namespace Lamoda\Metric\Storage\Decorators;

use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;
use Lamoda\Metric\Storage\AdjustableMetricStorageInterface;
use Lamoda\Metric\Storage\Exception\MetricStorageException;

final class ResolvableMetricSource implements \IteratorAggregate, MetricSourceInterface, AdjustableMetricStorageInterface
{
    /** @var MetricSourceInterface */
    private $delegate;

    /**
     * ResolvableMetricSource constructor.
     *
     * @param MetricSourceInterface $delegate
     */
    public function __construct(MetricSourceInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @return \Traversable|MetricInterface[]
     */
    public function getMetrics(): \Traversable
    {
        return $this->delegate->getMetrics();
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->delegate->getMetrics();
    }

    /** {@inheritdoc} */
    public function getAdjustableMetric(string $name, array $tags = []): MutableMetricInterface
    {
        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof MutableMetricInterface && $metric->getName() === $name) {
                return $metric;
            }
        }

        throw MetricStorageException::becauseUnknownKeyInStorage($name);
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key, array $tags = []): bool
    {
        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof MutableMetricInterface && $metric->getName() === $key) {
                return true;
            }
        }

        return false;
    }
}
