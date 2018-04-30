<?php

namespace Lamoda\Metric\Common\Tests\Source;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\MergingMetricSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Common\Source\MergingMetricSource
 */
final class MergingMetricSourceTest extends TestCase
{
    public function testMergingMetrics(): void
    {
        $source1 = $this->createMock([\IteratorAggregate::class, MetricSourceInterface::class]);
        $m1 = new Metric('test_1', 1.0);
        $m2 = new Metric('test_2', 2.0);
        $source1->method('getMetrics')->willReturn(new \ArrayIterator([$m1, $m2]));
        $source1->method('getIterator')->willReturn(new \ArrayIterator([$m1, $m2]));
        $source2 = $this->createMock([\IteratorAggregate::class, MetricSourceInterface::class]);
        $m3 = new Metric('test_3', 3.0);
        $m4 = new Metric('test_4', 4.0);
        $source2->method('getMetrics')->willReturn(new \ArrayIterator([$m3, $m4]));
        $source2->method('getIterator')->willReturn(new \ArrayIterator([$m3, $m4]));

        $source = new MergingMetricSource($source1, $source2);
        $metrics = $source->getMetrics();
        self::assertEquals($metrics, $source->getIterator());
        $metrics = iterator_to_array($metrics, false);
        self::assertCount(4, $metrics);
        self::assertContains($m1, $metrics);
        self::assertContains($m2, $metrics);
        self::assertContains($m3, $metrics);
        self::assertContains($m4, $metrics);
    }
}
