<?php

namespace Lamoda\Metric\Common\Tests;

use Lamoda\Metric\Common\Metric;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Common\Metric
 */
final class MetricTest extends TestCase
{
    public function testMetricAccessors(): void
    {
        $tags = ['tag' => 'value'];
        $value = 1.0;
        $name = 'test';
        $metric = new Metric($name, $value, $tags);
        self::assertSame($name, $metric->getName());
        self::assertSame($value, $metric->resolve());
        self::assertSame($tags, $metric->getTags());
    }

    public function testMetricAdjusting(): void
    {
        $metric = new Metric('test', 1.0);
        $metric->adjust(2.0);
        self::assertSame(3.0, $metric->resolve());
    }

    public function testMetricSetting(): void
    {
        $metric = new Metric('test', 1.0);
        $metric->setValue(2.0);
        self::assertSame(2.0, $metric->resolve());
    }
}
