<?php

namespace Lamoda\MetricInfra\Tests\Decorators;

use Lamoda\MetricInfra\Decorators\ResolvableContainerTrait;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricInfra\Decorators\ResolvableContainerTrait
 */
final class ResolvableContainerTraitTest extends TestCase
{
    public function testResolvableContainerTrait()
    {
        $key = 'test_metric';

        $metric1 = $this->getMockBuilder([MetricInterface::class, AdjustableMetricInterface::class])->getMock();
        $metric1->method('getName')->willReturn($key);

        $metric2 = $this->createMock(MetricInterface::class);
        $metric2->method('getName')->willReturn('another_metric');

        /** @var AdjustableMetricStorageInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockForTrait(ResolvableContainerTrait::class);
        $mock->method('getResolutionIterator')
            ->willReturn(new \ArrayIterator([$metric1, $metric2]));

        self::assertTrue($mock->hasAdjustableMetric($key));
        self::assertNotNull($mock->getAdjustableMetric($key));
    }

    /**
     * @expectedException \Lamoda\MetricStorage\Exception\MetricStorageException
     */
    public function testResolvableContainerTraitThrowsExceptionForUnknownKey()
    {
        /** @var AdjustableMetricStorageInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockForTrait(ResolvableContainerTrait::class);
        $mock->method('getResolutionIterator')
            ->willReturn(new \ArrayIterator());

        self::assertFalse($mock->hasAdjustableMetric('any_key'));
        $mock->getAdjustableMetric('any_key');
    }
}
