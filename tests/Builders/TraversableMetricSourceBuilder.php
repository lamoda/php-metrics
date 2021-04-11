<?php

declare(strict_types=1);

namespace Lamoda\Metric\Tests\Builders;

use Lamoda\Metric\Common\MetricSourceInterface;

final class TraversableMetricSourceBuilder
{
    public static function build(array $metrics): MetricSourceInterface
    {
        return new class($metrics) implements \IteratorAggregate, MetricSourceInterface {
            /** @var array */
            private $metrics;

            public function __construct(array $metrics)
            {
                $this->metrics = $metrics;
            }

            public function getMetrics(): \Traversable
            {
                return new \ArrayIterator($this->metrics);
            }

            public function getIterator(): \ArrayIterator
            {
                return new \ArrayIterator($this->metrics);
            }
        };
    }
}
