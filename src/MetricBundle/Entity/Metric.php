<?php

namespace Lamoda\Metric\MetricBundle\Entity;

use Lamoda\Metric\Storage\AdjustableMetricInterface;

abstract class Metric implements AdjustableMetricInterface
{
    /** @var string */
    private $key;
    /** @var float */
    private $value;
    /** @var string[] */
    private $tags;

    public function __construct(string $key, float $value, array $tags = [])
    {
        $this->key = $key;
        $this->value = $value;
        $this->tags = $tags;
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

    /** {@internal } */
    public function getTags(): array
    {
        return $this->tags;
    }
}
