<?php

namespace Lamoda\MetricInfra\Decorators;

use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;
use Lamoda\MetricStorage\Exception\MetricStorageException;

final class ResolvableMetricGroupSource implements \IteratorAggregate, MetricGroupSourceInterface, AdjustableMetricStorageInterface
{
    /** @var MetricGroupSourceInterface */
    private $delegate;

    /**
     * ResolvableMetricGroupSource constructor.
     *
     * @param MetricGroupSourceInterface $delegate
     */
    public function __construct(MetricGroupSourceInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /** {@inheritdoc} */
    public function all(): \Traversable
    {
        return $this->delegate->all();
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->delegate->all();
    }

    /** {@inheritdoc} */
    public function getAdjustableMetric(string $key): AdjustableMetricInterface
    {
        foreach ($this->delegate->all() as $group) {
            if ($group instanceof AdjustableMetricStorageInterface && $group->hasAdjustableMetric($key)) {
                return $group->getAdjustableMetric($key);
            }
        }

        throw MetricStorageException::becauseUnknownKeyInStorage($key);
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key): bool
    {
        foreach ($this->delegate->all() as $group) {
            if ($group instanceof AdjustableMetricStorageInterface && $group->hasAdjustableMetric($key)) {
                return true;
            }
        }

        return false;
    }
}
