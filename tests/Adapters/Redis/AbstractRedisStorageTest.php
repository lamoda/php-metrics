<?php

declare(strict_types=1);

namespace Adapters\Redis;

use Lamoda\Metric\Adapters\Redis\MetricDto;
use Lamoda\Metric\Adapters\Redis\MetricWrapper;
use Lamoda\Metric\Adapters\Redis\RedisConnectionInterface;
use Lamoda\Metric\Adapters\Redis\AbstractRedisStorage;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AbstractRedisStorageTest extends TestCase
{
    /** @var RedisConnectionInterface|MockObject */
    private $redisConnection;
    /** @var AbstractRedisStorage */
    private $redisStorage;

    protected function setUp(): void
    {
        $this->redisConnection = $this->createMock(RedisConnectionInterface::class);
        $this->redisStorage = $this->createRedisStorage();
    }

    public function testReceive(): void
    {
        $metrics = [
            $this->createMetric('test1', 17, ['source' => 'fast', 'path' => 'inner']),
            $this->createMetric('test2', 4, ['margin' => 'left']),
        ];

        $source = $this->createMock(MetricSourceInterface::class);
        $source
            ->expects($this->once())
            ->method('getMetrics')
            ->willReturn(new \ArrayIterator($metrics));

        $expectedDto = [
            new MetricDto('test1', 17, ['source' => 'fast', 'path' => 'inner']),
            new MetricDto('test2', 4, ['margin' => 'left']),
        ];
        $this->redisConnection
            ->expects($this->once())
            ->method('setMetrics')
            ->with($expectedDto);

        $this->redisStorage->receive($source);
    }

    public function testGetMetrics(): void
    {
        $this->redisConnection
            ->expects($this->once())
            ->method('getAllMetrics')
            ->willReturn([
                new MetricDto('test1', 17, ['source' => 'fast', 'path' => 'inner']),
                new MetricDto('test2', 4, ['margin' => 'left']),
            ]);

        $expected = [
            new MetricWrapper(
                $this->redisConnection,
                new Metric('test1', 17, ['source' => 'fast', 'path' => 'inner'])
            ),
            new MetricWrapper(
                $this->redisConnection,
                new Metric('test2', 4, ['margin' => 'left'])
            ),
        ];

        $actual = $this->redisStorage->getMetrics();

        self::assertEquals($expected, iterator_to_array($actual));
    }

    public function testFindMetric(): void
    {
        $this->redisConnection
            ->expects($this->once())
            ->method('getMetricValue')
            ->with('test1', ['source' => 'fast', 'path' => 'inner'])
            ->willReturn(4.0);

        $expected = new MetricWrapper(
            $this->redisConnection,
            new Metric('test1', 4, ['source' => 'fast', 'path' => 'inner'])
        );

        $actual = $this->redisStorage->findMetric('test1', ['source' => 'fast', 'path' => 'inner']);

        self::assertEquals($expected, $actual);
    }

    public function testFindMetricWhenMetricIsNotFound(): void
    {
        $this->redisConnection
            ->expects($this->once())
            ->method('getMetricValue')
            ->with('test1', ['source' => 'fast', 'path' => 'inner'])
            ->willReturn(null);

        $actual = $this->redisStorage->findMetric('test1', ['source' => 'fast', 'path' => 'inner']);

        self::assertNull($actual);
    }

    public function testCreateMetric(): void
    {
        $expectedDto = new MetricDto('test1', 17, ['source' => 'fast', 'path' => 'inner']);
        $this->redisConnection
            ->expects($this->once())
            ->method('setMetrics')
            ->with([$expectedDto]);

        $expected = new MetricWrapper(
            $this->redisConnection,
            new Metric('test1', 17, ['source' => 'fast', 'path' => 'inner'])
        );

        $actual = $this->redisStorage->createMetric('test1', 17, ['source' => 'fast', 'path' => 'inner']);

        self::assertEquals($expected, $actual);
    }

    public function testSetMetricValue(): void
    {
        $expectedDto = new MetricDto('test1', 17, ['source' => 'fast', 'path' => 'inner']);
        $this->redisConnection
            ->expects($this->once())
            ->method('setMetrics')
            ->with([$expectedDto]);

        $this->redisStorage->setMetricValue('test1', 17, ['source' => 'fast', 'path' => 'inner']);
    }

    public function testAdjustMetricValue(): void
    {
        $this->redisConnection
            ->expects($this->once())
            ->method('adjustMetric')
            ->with('test1', 17, ['source' => 'fast', 'path' => 'inner']);

        $this->redisStorage->adjustMetricValue('test1', 17, ['source' => 'fast', 'path' => 'inner']);
    }

    private function createMetric(string $name, float $value, array $tags)
    {
        $metric = $this->createMock(MetricInterface::class);
        $metric
            ->method('getName')
            ->willReturn($name);
        $metric
            ->method('resolve')
            ->willReturn($value);
        $metric
            ->method('getTags')
            ->willReturn($tags);

        return $metric;
    }

    private function createRedisStorage(): AbstractRedisStorage
    {
        return new class($this->redisConnection) extends AbstractRedisStorage
        {
            protected function doCreateMetric(string $name, float $value, array $tags = []): MutableMetricInterface
            {
                return new Metric($name, $value, $tags);
            }
        };
    }
}
