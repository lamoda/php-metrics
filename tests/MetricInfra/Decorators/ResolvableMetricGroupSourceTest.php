<?php

namespace Lamoda\MetricInfra\Tests\Decorators;

use Lamoda\MetricInfra\Decorators\ResolvableMetricGroup;
use Lamoda\MetricInfra\Decorators\ResolvableMetricGroupSource;
use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricInfra\Decorators\ResolvableMetricGroupSource
 */
final class ResolvableMetricGroupSourceTest extends TestCase
{
    public function testDecoratedMethods()
    {
        /** @var MetricGroupSourceInterface|\PHPUnit_Framework_MockObject_MockObject $decorated */
        $decorated = $this->createMock(MetricGroupSourceInterface::class);

        $decorator = new ResolvableMetricGroupSource($decorated);

        $adjustableMetric = $this->getMockBuilder(
            [
                AdjustableMetricInterface::class,
                MetricInterface::class,
            ]
        )->getMock();
        $adjustableMetric->method('getName')->willReturn('adjustable_metric');

        $nonAdjustableMetric = $this->createMock(MetricInterface::class);
        $nonAdjustableMetric->method('getName')->willReturn('non_adjustable_metric');

        $group = new CombinedMetricGroup('test_group');
        $group->addMetric($adjustableMetric);
        $group->addMetric($nonAdjustableMetric);
        $groups = new \ArrayIterator([new ResolvableMetricGroup($group)]);
        $decorated->method('all')->willReturn($groups);

        self::assertSame($groups, $decorator->all());
        self::assertSame($groups, $decorator->getIterator());
        self::assertTrue($decorator->hasAdjustableMetric('adjustable_metric'));
        self::assertSame($adjustableMetric, $decorator->getAdjustableMetric('adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('non_adjustable_metric'));
        self::assertFalse($decorator->hasAdjustableMetric('any_metric'));
    }
}
