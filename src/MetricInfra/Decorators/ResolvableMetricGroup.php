<?php

namespace Lamoda\MetricInfra\Decorators;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;

final class ResolvableMetricGroup implements \IteratorAggregate, MetricGroupInterface, AdjustableMetricStorageInterface
{
    use ResolvableContainerTrait;

    /** @var MetricGroupInterface */
    private $delegate;

    /**
     * ResolvableMetricGroup constructor.
     *
     * @param MetricGroupInterface $delegate
     */
    public function __construct(MetricGroupInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->delegate->getName();
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        return $this->delegate->getMetrics();
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return $this->delegate->getTags();
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
