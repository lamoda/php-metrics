<?php

namespace Lamoda\Metric\Common\Tests;

use Lamoda\Metric\Common\DefaultTagsMetric;
use Lamoda\Metric\Common\MetricInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Common\DefaultTagsMetric
 */
final class DefaultTagsMetricTest extends TestCase
{
    public function testCallsAreProxied(): void
    {
        $name = 'test';
        $value = 1.0;
        $tags = ['tag' => 'value'];
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects($this->once())->method('getName')->willReturn($name);
        $metric->expects($this->once())->method('resolve')->willReturn($value);
        $metric->expects($this->once())->method('getTags')->willReturn($tags);

        $wrapper = new DefaultTagsMetric($metric);

        self::assertSame($name, $wrapper->getName());
        self::assertSame($value, $wrapper->resolve());
        self::assertSame($tags, $wrapper->getTags());
    }

    public function testExtraTagsAdded(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects($this->once())->method('getTags')->willReturn(['tag' => 'value']);

        $wrapper = new DefaultTagsMetric($metric, ['extra' => 'tag']);

        self::assertSame(['extra' => 'tag', 'tag' => 'value'], $wrapper->getTags());
    }

    public function testExistingTagsAreNotOverwritten(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $metric->expects($this->once())->method('getTags')->willReturn(['tag' => 'value']);

        $wrapper = new DefaultTagsMetric($metric, ['tag' => 'new_value']);

        self::assertSame(['tag' => 'value'], $wrapper->getTags());
    }
}
