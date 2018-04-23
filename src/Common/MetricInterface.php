<?php

namespace Lamoda\Metric\Common;

interface MetricInterface
{
    /**
     * Returns string metric key identifier.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns metric value.
     *
     * @return float
     */
    public function resolve(): float;

    /**
     * Returns metric tags.
     *
     * @return string[]
     */
    public function getTags(): array;
}
