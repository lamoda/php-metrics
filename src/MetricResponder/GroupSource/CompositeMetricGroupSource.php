<?php

namespace Lamoda\MetricResponder\GroupSource;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;

final class CompositeMetricGroupSource implements \IteratorAggregate, MetricGroupSourceInterface
{
    /** @var MetricGroupSourceInterface[] */
    private $sources;
    /** @var MetricGroupInterface[] */
    private $groups;

    /**
     * GroupSourceHolder constructor.
     *
     * @param MetricGroupSourceInterface[] $sources
     * @param MetricGroupInterface[]       $groups
     */
    public function __construct(array $sources = [], array $groups = [])
    {
        $this->sources = $sources;
        $this->groups = $groups;
    }

    public function addSource(MetricGroupSourceInterface $source)
    {
        $this->sources[] = $source;
    }

    public function addGroup(MetricGroupInterface $group)
    {
        $this->groups[] = $group;
    }

    public function all(): \Traversable
    {
        foreach ($this->getSources() as $source) {
            yield from $source;
        }
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->all();
    }

    /**
     * @return MetricGroupSourceInterface[]
     */
    private function getSources(): array
    {
        $sources = $this->sources;

        $sources[] = new ArrayMetricGroupSource($this->groups);

        return $sources;
    }
}
