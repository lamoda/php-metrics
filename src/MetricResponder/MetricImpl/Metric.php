<?php

namespace Lamoda\MetricResponder\MetricImpl;

use Lamoda\MetricResponder\MetricInterface;

final class Metric implements MetricInterface
{
    /** @var string */
    private $name;
    /** @var float|iterable|MetricInterface[]|\Traversable */
    private $value;

    /**
     * Metric constructor.
     *
     * @param string $name
     * @param float  $value
     */
    public function __construct(string $name, float $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function resolve(): float
    {
        return $this->value;
    }
}
