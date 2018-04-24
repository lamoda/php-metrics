<?php

namespace Lamoda\Metric\Storage\Tests\Decorators;

use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Storage\MutableMetricInterface;
use Lamoda\Metric\Storage\Decorators\ResolvableMetricSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Storage\Decorators\ResolvableMetricSource
 */
final class ResolvableMetricSourceTest extends TestCase
{
    public function testDecoratedMethods()
    {
        $adjustableMetric = $this->createMock(MutableMetricInterface::class);
        $adjustableMetric->method('getName')->willReturn('adjustable_metric');

        $nonAdjustableMetric = $this->createMock(MetricInterface::class);
        $nonAdjustableMetric->method('getName')->willReturn('non_adjustable_metric');

        $metrics = new \ArrayIterator([$adjustableMetric, $nonAdjustableMetric]);
        $decorated = new IterableMetricSource($metrics);
        $decorator = new ResolvableMetricSource($decorated);

        self::assertSame($metrics, $decorator->getMetrics());
        self::assertSame($metrics, $decorator->getIterator());
        self::assertTrue($decorator->hasAdjustableMetric('adjustable_metric'));
        self::assertSame($adjustableMetric, $decorator->getAdjustableMetric('adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('non_adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('any_metric'));
    }
}
