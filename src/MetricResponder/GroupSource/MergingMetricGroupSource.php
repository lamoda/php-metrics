<?php

namespace Lamoda\MetricResponder\GroupSource;

use Lamoda\MetricResponder\MetricGroup\MergingMetricGroup;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;

final class MergingMetricGroupSource implements \IteratorAggregate, MetricGroupSourceInterface
{
    /** @var MetricGroupInterface[][] */
    private $groups = [];

    /** {@inheritdoc} */
    public function all(): \Traversable
    {
        foreach ($this->groups as $name => $groups) {
            yield new MergingMetricGroup($name, $groups);
        }
    }

    public function register(MetricGroupInterface $group)
    {
        $this->groups[$group->getName()][] = $group;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->all();
    }
}
