<?php

namespace Lamoda\Metric\Storage;

final class MetricAdjuster implements MetricAdjusterInterface
{
    /** @var AdjustableMetricStorageInterface */
    private $storage;

    public function __construct(AdjustableMetricStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /** {@inheritdoc} */
    public function adjustMetric(string $key, float $delta)
    {
        $this->storage->getAdjustableMetric($key)->adjust($delta);
    }
}
