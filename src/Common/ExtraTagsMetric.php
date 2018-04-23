<?php

namespace Lamoda\Metric\Common;

final class ExtraTagsMetric implements MetricInterface
{
    /** @var MetricInterface */
    private $metric;
    /**
     * @var array
     */
    private $tags;

    /**
     * MetricWrapper constructor.
     *
     * @param MetricInterface $metric
     * @param string[]        $tags
     */
    public function __construct(MetricInterface $metric, array $tags = [])
    {
        $this->metric = $metric;
        $this->tags = $tags;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->metric->getName();
    }

    /** {@inheritdoc} */
    public function resolve(): float
    {
        return $this->metric->resolve();
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return array_replace($this->tags, $this->metric->getTags());
    }
}
