<?php

namespace Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity;

use Lamoda\Metric\Storage\MutableMetricInterface;

class Metric implements MutableMetricInterface
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var float */
    private $value;
    /** @var string[] */
    private $tags;

    public function __construct(string $key, float $value, array $tags = [])
    {
        $this->name = $key;
        $this->value = $value;
        $this->tags = $tags;
        ksort($this->tags);
    }

    public function getId(): string
    {
        return $this->id;
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

    /** {@inheritdoc} */
    public function adjust(float $delta): void
    {
        $this->value += $delta;
    }

    /** {@inheritdoc} */
    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    /** {@internal} */
    public function getTags(): array
    {
        return $this->tags;
    }
}
