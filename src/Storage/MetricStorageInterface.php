<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Common\MetricSourceInterface;

interface MetricStorageInterface extends MetricSourceInterface, MetricReceiverInterface
{
    /**
     * Find metric by name and tags.
     *
     * @param string   $name
     * @param string[] $tags
     *
     * @return MutableMetricInterface|null
     */
    public function findMetric(string $name, array $tags = []): ?MutableMetricInterface;

    /**
     * Create metric instance by containment.
     *
     * @param string   $name
     * @param float    $value
     * @param string[] $tags
     *
     * @return MutableMetricInterface
     */
    public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface;
}
