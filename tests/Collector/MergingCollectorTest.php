<?php

namespace Lamoda\Metric\Collector\Tests;

use Lamoda\Metric\Collector\MergingCollector;
use Lamoda\Metric\Collector\SingleSourceCollector;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Collector\MergingCollector
 */
final class MergingCollectorTest extends TestCase
{
    public function testCollectorCollectsSourcesFromDelegate(): void
    {
        $originalMetric11 = new Metric('1_1', 1.0);
        $originalMetric12 = new Metric('1_2', 2.0);
        $originalSource1 = new IterableMetricSource([$originalMetric11, $originalMetric12]);

        $originalMetric21 = new Metric('2_1', 3.0);
        $originalMetric22 = new Metric('2_2', 4.0);
        $originalSource2 = new IterableMetricSource([$originalMetric21, $originalMetric22]);

        $collector1 = new SingleSourceCollector($originalSource1);
        $collector2 = new SingleSourceCollector($originalSource2);

        $collector = new MergingCollector([$collector1, $collector2]);

        $source = $collector->collect();
        $metrics = $source->getMetrics();
        $metrics = iterator_to_array($metrics, false);

        self::assertCount(4, $metrics);
        foreach ([$originalMetric11, $originalMetric12, $originalMetric21, $originalMetric22] as $originalMetric) {
            self::assertContains($originalMetric, $metrics);
        }
    }
}
