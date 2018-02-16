<?php

namespace Lamoda\MetricInfra\Tests\Decorators;

use Lamoda\MetricInfra\Decorators\ResolvableMetricSource;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\MetricSourceInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricInfra\Decorators\ResolvableMetricSource
 */
final class ResolvableMetricSourceTest extends TestCase
{
    public function testDecoratedMethods()
    {
        /** @var MetricSourceInterface|\PHPUnit_Framework_MockObject_MockObject $decorator */
        $decorated = $this->createMock(MetricSourceInterface::class);

        $decorator = new ResolvableMetricSource($decorated);

        $adjustableMetric = $this->getMockBuilder(
            [
                AdjustableMetricInterface::class,
                MetricInterface::class,
            ]
        )->getMock();
        $adjustableMetric->method('getName')->willReturn('adjustable_metric');

        $nonAdjustableMetric = $this->createMock(MetricInterface::class);
        $nonAdjustableMetric->method('getName')->willReturn('non_adjustable_metric');

        $metrics = new \ArrayIterator([$adjustableMetric, $nonAdjustableMetric]);
        $decorated->method('getMetrics')->willReturn($metrics);

        self::assertSame($metrics, $decorator->getMetrics());
        self::assertSame($metrics, $decorator->getIterator());
        self::assertTrue($decorator->hasAdjustableMetric('adjustable_metric'));
        self::assertSame($adjustableMetric, $decorator->getAdjustableMetric('adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('non_adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('any_metric'));
    }
}
