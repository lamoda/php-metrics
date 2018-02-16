<?php

namespace Lamoda\MetricBundle\Tests\Controller;

use Lamoda\MetricBundle\Controller\ResponderController;
use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricImpl\Metric;
use Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class ResponderControllerTest extends TestCase
{
    public function testControllerProducesJsonResult()
    {
        $m1 = new Metric('m1', 1.1);
        $m2 = new Metric('m2', 2);

        $expected
            = /** @lang JSON */
            <<<JSON
[
    {
      "m1":1.1,
      "m2":2,
      "t1":"tag"
    },
    {
      "m2": 2
    },
    {
      "m1": 1.1  
    }
]
JSON;

        /** @var MetricGroupSourceInterface|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMockBuilder([MetricGroupSourceInterface::class, \IteratorAggregate::class])->getMock();

        $g1 = new CombinedMetricGroup('test1', ['t1' => 'tag']);
        $g1->addMetric($m1);
        $g1->addMetric($m2);
        $g2 = new CombinedMetricGroup('test2');
        $g2->addMetric($m2);
        $g3 = new CombinedMetricGroup('test3');
        $g3->addMetric($m1);

        $source->method('getIterator')->willReturn(new \ArrayIterator([$g1, $g2, $g3]));

        $controller = new ResponderController($source, new TelegrafResponseFactory());

        $response = $controller();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
