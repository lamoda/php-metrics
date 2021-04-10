<?php

namespace Lamoda\Metric\Responder\Tests\ResponseFactory;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Responder\ResponseFactory\PrometheusResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Responder\ResponseFactory\PrometheusResponseFactory
 */
class PrometheusResponseFactoryTest extends TestCase
{
    public function testResponseFormat(): void
    {
        $source = new IterableMetricSource(
            [
                new Metric('metrics_orders', 200.0, ['country' => 'ru']),
                new Metric('metrics_errors', 0.0, ['country' => 'ru']),
                new Metric('untagged_metric', 5.0),
            ]
        );

        $response = (new PrometheusResponseFactory())->create($source);
        $data = (string) $response->getBody();
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertSame(
            <<<PROMETHEUS
metrics_orders{country="ru"} 200
metrics_errors{country="ru"} 0
untagged_metric 5

PROMETHEUS
            ,
            $data
        );
    }
}
