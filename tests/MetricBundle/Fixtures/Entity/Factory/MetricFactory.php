<?php

namespace Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Factory;

use Lamoda\Metric\Common\Factory\MetricFactoryInterface;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;

final class MetricFactory implements MetricFactoryInterface
{
    /** {@inheritdoc} */
    public function createMetric(string $name, float $value, array $tags = []): MetricInterface
    {
        return new Metric($name, $value);
    }
}
