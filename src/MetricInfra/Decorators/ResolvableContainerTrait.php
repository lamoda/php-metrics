<?php

namespace Lamoda\MetricInfra\Decorators;

use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use Lamoda\MetricStorage\Exception\MetricStorageException;

trait ResolvableContainerTrait
{
    /** {@inheritdoc} */
    public function getAdjustableMetric(string $key): AdjustableMetricInterface
    {
        foreach ($this->getResolutionIterator() as $metric) {
            if ($metric instanceof AdjustableMetricInterface && $metric->getName() === $key) {
                return $metric;
            }
        }

        throw MetricStorageException::becauseUnknownKeyInStorage($key);
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key): bool
    {
        foreach ($this->getResolutionIterator() as $metric) {
            if ($metric instanceof AdjustableMetricInterface && $metric->getName() === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates iterator to check if elements store the necessary metric.
     *
     * @return MetricInterface[]|\Traversable
     */
    abstract protected function getResolutionIterator(): \Traversable;
}
