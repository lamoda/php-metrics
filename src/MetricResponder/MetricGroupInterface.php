<?php

namespace Lamoda\MetricResponder;

interface MetricGroupInterface extends \Traversable
{
    /**
     * Returns string group identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns iterable of metrics.
     *
     * @return MetricInterface[]|iterable|\Traversable
     */
    public function getMetrics(): \Traversable;

    /**
     * Returns array of string tags.
     *
     * @return string[]
     */
    public function getTags(): array;
}
