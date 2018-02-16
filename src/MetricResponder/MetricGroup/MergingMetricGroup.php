<?php

namespace Lamoda\MetricResponder\MetricGroup;

use Lamoda\MetricResponder\MetricGroupInterface;

final class MergingMetricGroup implements \IteratorAggregate, MetricGroupInterface
{
    /** @var string */
    private $name;
    /** @var MetricGroupInterface[] */
    private $groups;
    /** @var string[] */
    private $tags;

    /**
     * MergingMetricGroup constructor.
     *
     * @param string                                       $name
     * @param MetricGroupInterface[]|\Traversable|iterable $groups
     */
    public function __construct($name, $groups)
    {
        $this->name = $name;
        $this->groups = $groups;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        if (null === $this->tags) {
            $this->tags = [];

            foreach ($this->groups as $group) {
                foreach ($group->getTags() as $tag => $value) {
                    $this->tags[$tag] = $value;
                }
            }
        }

        return $this->tags;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        foreach ($this->groups as $group) {
            yield from $group->getMetrics();
        }
    }
}
