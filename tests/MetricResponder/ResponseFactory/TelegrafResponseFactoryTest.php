<?php

namespace Lamoda\MetricResponder\Tests\ResponseFactory;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory
 */
final class TelegrafResponseFactoryTest extends TestCase
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

        /** @var MetricGroupSourceInterface | MockObject $groupSource */
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

        $data = (string) (new TelegrafResponseFactory())->create($groupSource)->getBody();
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    [
                        'metrics_orders' => 200.0,
                        'metrics_errors' => 0.0,
                        'country' => 'ru',
                    ],
                ]
            ),
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
