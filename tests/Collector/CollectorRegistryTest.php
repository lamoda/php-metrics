<?php

namespace Lamoda\Metric\Collector\Tests;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Collector\MetricCollectorInterface;
use PHPUnit\Framework\TestCase;

final class CollectorRegistryTest extends TestCase
{
    public function testRegistryStoresValues(): void
    {
        $mock = $this->createMock(MetricCollectorInterface::class);

        $registry = new CollectorRegistry();
        $registry->register('test', $mock);

        self::assertSame($mock, $registry->getCollector('test'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testRegistryThrowsExceptionsForUnknownCollector(): void
    {
        $registry = new CollectorRegistry();

        $registry->getCollector('test');
    }
}
