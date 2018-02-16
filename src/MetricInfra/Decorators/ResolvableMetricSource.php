<?php

namespace Lamoda\MetricInfra\Decorators;

use Lamoda\MetricResponder\MetricSourceInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;

final class ResolvableMetricSource implements \IteratorAggregate, MetricSourceInterface, AdjustableMetricStorageInterface
{
    use ResolvableContainerTrait;
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

    /** {@inheritdoc} */
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
    protected function getResolutionIterator(): \Traversable
    {
        return $this->delegate->getMetrics();
    }
}
