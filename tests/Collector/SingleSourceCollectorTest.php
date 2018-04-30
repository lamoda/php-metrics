<?php

namespace Lamoda\Metric\Collector\Tests;

use Lamoda\Metric\Collector\SingleSourceCollector;
use Lamoda\Metric\Common\MetricSourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Collector\SingleSourceCollector
 */
final class SingleSourceCollectorTest extends TestCase
{
    public function testCollectorHoldsSource(): void
    {
        $source = $this->createMock(MetricSourceInterface::class);
        $collector = new SingleSourceCollector($source);
        self::assertSame($source, $collector->collect());
    }
}
