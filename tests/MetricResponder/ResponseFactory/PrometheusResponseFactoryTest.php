<?php

namespace Lamoda\MetricResponder\Tests\ResponseFactory;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\ResponseFactory\PrometheusResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricResponder\ResponseFactory\PrometheusResponseFactory
 */
class PrometheusResponseFactoryTest extends TestCase
{
    public function testNormalize()
    {
        $group = $this->getMockBuilder(
            [
                MetricGroupInterface::class,
                \IteratorAggregate::class,
            ]
        )->getMock();
        $group
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(
                new \ArrayObject(
                    [
                        $this->mockMetric('metrics_orders', 200.0),
                        $this->mockMetric('metrics_errors', 0.0),
                    ]
                )
            );
        $group
            ->expects($this->once())
            ->method('getTags')
            ->willReturn(['country' => 'ru']);

        $groupSource = $this->getMockBuilder(
            [
                MetricGroupSourceInterface::class,
                \IteratorAggregate::class,
            ]
        )->getMock();
        $groupSource
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$group]));

        $data = (string) (new PrometheusResponseFactory())->create($groupSource)->getBody();
        $this->assertSame(
            <<<PROMETHEUS
metrics_orders{country="ru"} 200
metrics_errors{country="ru"} 0

PROMETHEUS
            ,
            $data
        );
    }

    private function mockMetric(string $name, float $value): MetricInterface
    {
        $metric = $this->createMock(MetricInterface::class);

        $metric
            ->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $metric
            ->expects($this->once())
            ->method('resolve')
            ->willReturn($value);

        return $metric;
    }
}
