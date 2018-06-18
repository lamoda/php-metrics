<?php

namespace Lamoda\Metric\Storage;

final class StoredMetricMutator implements MetricMutatorInterface
{
    /** @var MetricStorageInterface */
    private $storage;

    public function __construct(MetricStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /** {@inheritdoc} */
    public function adjustMetricValue(float $delta, string $name, array $tags = []): void
    {
        $this->findOrCreateMetric($name, $tags)->adjust($delta);
    }

    /** {@inheritdoc} */
    public function setMetricValue(float $value, string $name, array $tags = []): void
    {
        $this->findOrCreateMetric($name, $tags)->setValue($value);
    }

    private function findOrCreateMetric(string $name, array $tags): MutableMetricInterface
    {
        return $this->storage->findMetric($name, $tags) ?: $this->storage->createMetric($name, 0, $tags);
    }
}
