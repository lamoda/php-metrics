<?php

namespace Lamoda\MetricBundle\Entity;

use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;

abstract class Metric implements AdjustableMetricInterface, MetricInterface
{
    /** @var string */
    private $key;
    /** @var float */
    private $value;

    public function __construct(string $key, float $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->key;
    }

    /** {@inheritdoc} */
    public function resolve(): float
    {
        return $this->value;
    }

    /** {@inheritdoc} */
    public function adjust(float $delta)
    {
        $this->value += $delta;
    }
}
