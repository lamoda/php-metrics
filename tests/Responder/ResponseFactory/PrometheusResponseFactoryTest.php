<?php

namespace Lamoda\Metric\Responder\Tests\ResponseFactory;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Responder\MetricGroupInterface;
use Lamoda\Metric\Responder\MetricGroupSourceInterface;
use Lamoda\Metric\Responder\ResponseFactory\PrometheusResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Responder\ResponseFactory\PrometheusResponseFactory
 */
class PrometheusResponseFactoryTest extends TestCase
{
    public function testNormalize()
    {
        $source = new IterableMetricSource(
            [
                new Metric('metrics_orders', 200.0, ['country' => 'ru']),
                new Metric('metrics_errors', 0.0, ['country' => 'ru']),
            ]
        );

        $data = (string) (new PrometheusResponseFactory())->create($source)->getBody();
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
