<?php

namespace Lamoda\MetricResponder;

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
}
