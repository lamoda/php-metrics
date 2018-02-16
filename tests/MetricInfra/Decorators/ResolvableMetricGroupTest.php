<?php

namespace Lamoda\MetricInfra\Tests\Decorators;

use Lamoda\MetricInfra\Decorators\ResolvableMetricGroup;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricInfra\Decorators\ResolvableMetricGroup
 */
final class ResolvableMetricGroupTest extends TestCase
{
    public function testDecoratedMethods()
    {
        /** @var MetricGroupInterface|\PHPUnit_Framework_MockObject_MockObject $decorated */
        $decorated = $this->createMock(MetricGroupInterface::class);

        $decorator = new ResolvableMetricGroup($decorated);

        $name = 'test_group';
        $decorated->expects($this->once())->method('getName')->willReturn($name);
        self::assertSame($name, $decorator->getName());

        $tags = ['tag1' => 'tag1_value'];
        $decorated->expects($this->once())->method('getTags')->willReturn($tags);
        self::assertSame($tags, $decorator->getTags());

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
