<?php

namespace Lamoda\Metric\MetricBundle\Tests\Controller;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\MetricBundle\Controller\HttpFoundationResponder;
use Lamoda\Metric\Responder\PsrResponder;
use Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\MetricBundle\Controller\HttpFoundationResponder
 */
final class HttpFoundationResponderTest extends TestCase
{
    public function testControllerProducesJsonResult()
    {
        $m1 = new Metric('m1', 1.1);
        $m2 = new Metric('m2', 2);

        $expected
            = /** @lang JSON */
            <<<JSON
    {
      "m1":1.1,
      "m2":2
    }
JSON;
        $source = new IterableMetricSource([$m1, $m2]);

        $collector = $this->createMock(MetricCollectorInterface::class);
        $collector->method('collect')->willReturn($source);

        $controller = new HttpFoundationResponder(new PsrResponder($collector, new TelegrafJsonResponseFactory()));

        $response = $controller->createResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
