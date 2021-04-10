<?php

namespace Lamoda\Metric\Storage\Tests;

use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Storage\StorageRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Storage\StorageRegistry
 */
final class StorageRegistryTest extends TestCase
{
    public function testRegistryStoresValues(): void
    {
        $mock = $this->createMock(MetricStorageInterface::class);

        $registry = new StorageRegistry();
        $registry->register('test', $mock);

        self::assertSame($mock, $registry->getStorage('test'));
    }

    public function testRegistryThrowsExceptionsForUnknownCollector(): void
    {
        $registry = new StorageRegistry();

        $this->expectException(\OutOfBoundsException::class);
        $registry->getStorage('test');
    }
}
