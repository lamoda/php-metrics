<?php

namespace Lamoda\Metric\Storage;

final class MetricMutator implements MetricMutatorInterface
{
    /** @var MetricStorageInterface */
    private $storage;

    public function __construct(MetricStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /** {@inheritdoc} */
    public function adjustMetric(string $name, float $delta, array $tags = []): void
    {
        $this->findOrCreateMetric($name, $tags)->adjust($delta);
    }

    /** {@inheritdoc} */
    public function setMetric(string $name, float $value, array $tags = []): void
    {
        $this->findOrCreateMetric($name, $tags)->setValue($value);
    }

    private function findOrCreateMetric(string $name, array $tags): MutableMetricInterface
    {
        return $this->storage->findMetric($name, $tags) ?: $this->storage->createMetric($name, 0, $tags);
    }
}
