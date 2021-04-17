<?php

declare(strict_types=1);

namespace Adapters\Redis;

use Lamoda\Metric\Adapters\Redis\MetricDto;
use Lamoda\Metric\Adapters\Redis\RedisConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RedisConnectionTest extends TestCase
{
    private const METRICS_KEY = 'metrics_key';
    /** @var \Redis|MockObject */
    private $redis;
    /** @var RedisConnection */
    private $redisConnection;

    protected function setUp(): void
    {
        $this->redis = $this->createMock(\Redis::class);
        $this->redisConnection = new RedisConnection($this->redis, static::METRICS_KEY);
    }

    public function testReceivingAllMetrics(): void
    {
        $this->redis
            ->expects($this->once())
            ->method('hgetall')
            ->with(static::METRICS_KEY)
            ->willReturn([
                '{"name":"test1","tags":"{\"status\":15,\"port\":1}"}' => '17',
                '{"name":"test2","tags":"{\"severity\":\"high\"}"}' => '2',
            ]);

        $expected = [
            new MetricDto('test1', 17, ['status' => 15, 'port' => 1]),
            new MetricDto('test2', 2, ['severity' => 'high']),
        ];
        $actual = $this->redisConnection->getAllMetrics();

        self::assertEquals($expected, $actual);
    }

    public function testAdjustMetric(): void
    {
        $value = 15;
        $expectedField = '{"name":"test","tags":"{\"severity\":\"high\"}"}';
        $this->redis
            ->expects($this->once())
            ->method('hincrbyfloat')
            ->with(static::METRICS_KEY, $expectedField, $value)
            ->willReturn(17);

        $actual = $this->redisConnection->adjustMetric('test', $value, ['severity' => 'high']);
        self::assertEquals(17, $actual);
    }

    public function testSetMetrics(): void
    {
        $fields = [
            '{"name":"test1","tags":"{\"port\":1,\"status\":15}"}' => 17,
            '{"name":"test2","tags":"{\"severity\":\"high\"}"}' => 2,
        ];
        $this->redis
            ->expects($this->once())
            ->method('hmset')
            ->with(static::METRICS_KEY, $fields)
            ->willReturn(false);
        $metrics = [
            new MetricDto('test1', 17, ['status' => 15, 'port' => 1]),
            new MetricDto('test2', 2, ['severity' => 'high']),
        ];

        $this->redisConnection->setMetrics($metrics);
    }

    public function testGetMetricValue(): void
    {
        $expectedField = '{"name":"test","tags":"{\"severity\":\"high\"}"}';
        $this->redis
            ->expects($this->once())
            ->method('hget')
            ->with(static::METRICS_KEY, $expectedField)
            ->willReturn('17');

        $actual = $this->redisConnection->getMetricValue('test', ['severity' => 'high']);
        self::assertEquals(17, $actual);
    }

    public function testFailedGetMetricValue(): void
    {
        $expectedField = '{"name":"test","tags":"{\"severity\":\"high\"}"}';
        $this->redis
            ->expects($this->once())
            ->method('hget')
            ->with(static::METRICS_KEY, $expectedField)
            ->willReturn(false);

        $actual = $this->redisConnection->getMetricValue('test', ['severity' => 'high']);
        self::assertEquals(0, $actual);
    }
}
