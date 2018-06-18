<?php

namespace Lamoda\Metric\Responder\Tests;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Responder\PsrResponder;
use Lamoda\Metric\Responder\ResponseFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Lamoda\Metric\Responder\PsrResponder
 */
final class PsrResponderTest extends TestCase
{
    public function testResponderCollectsMetricsAndFormatsThem(): void
    {
        $options = ['prefix' => 'metric_'];

        $source = $this->createMock(MetricSourceInterface::class);
        $collector = $this->createMock(MetricCollectorInterface::class);
        $collector->expects($this->once())->method('collect')->willReturn($source);

        $factory = $this->createMock(ResponseFactoryInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $factory->expects($this->once())->method('create')
            ->with($source, $options)
            ->willReturn($response);

        $responder = new PsrResponder($collector, $factory, $options);
        self::assertSame($response, $responder->createResponse());
    }
}
