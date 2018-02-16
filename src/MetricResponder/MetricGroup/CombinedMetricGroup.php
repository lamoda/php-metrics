<?php

namespace Lamoda\MetricResponder\MetricGroup;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\MetricSource\CompositeMetricSource;
use Lamoda\MetricResponder\MetricSourceInterface;

final class CombinedMetricGroup implements \IteratorAggregate, MetricGroupInterface
{
    /** @var string */
    private $name;
    /** @var MetricSourceInterface[] */
    private $sources = [];
    /** @var MetricInterface[] */
    private $metrics = [];
    /** @var string[] */
    private $tags;

    /**
     * @param string   $name
     * @param string[] $tags
     */
    public function __construct(string $name, array $tags = [])
    {
        $this->name = $name;
        $this->tags = $tags;
    }

    /**
     * @param MetricSourceInterface $source
     */
    public function addSource(MetricSourceInterface $source)
    {
        $this->sources[] = $source;
    }

    /**
     * @param MetricInterface $metric
     */
    public function addMetric(MetricInterface $metric)
    {
        $this->metrics[] = $metric;
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return $this->tags;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        foreach ($this->getSources() as $source) {
            yield from $source;
        }
    }

    /**
     * @return MetricSourceInterface[]
     */
    private function getSources(): array
    {
        $sources = $this->sources;
        $sources[] = new CompositeMetricSource(new \ArrayIterator($this->metrics));

        return $sources;
    }
}
