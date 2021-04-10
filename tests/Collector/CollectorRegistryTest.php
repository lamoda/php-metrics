<?php

namespace Lamoda\Metric\Collector\Tests;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Collector\MetricCollectorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Collector\CollectorRegistry
 */
final class CollectorRegistryTest extends TestCase
{
    public function testRegistryStoresValues(): void
    {
        $mock = $this->createMock(MetricCollectorInterface::class);

        $registry = new CollectorRegistry();
        $registry->register('test', $mock);

        self::assertSame($mock, $registry->getCollector('test'));
    }

    public function testRegistryThrowsExceptionsForUnknownCollector(): void
    {
        $registry = new CollectorRegistry();

        $this->expectException(\OutOfBoundsException::class);
        $registry->getCollector('test');
    }
}
