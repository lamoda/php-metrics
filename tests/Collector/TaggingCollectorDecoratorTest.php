<?php

namespace Lamoda\Metric\Collector\Tests;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Collector\TaggingCollectorDecorator;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Collector\TaggingCollectorDecorator
 */
final class TaggingCollectorDecoratorTest extends TestCase
{
    public function testTagsAddedToSouredMetrics(): void
    {
        $metric1 = new Metric('test_1', 1.0, ['common' => 'tag']);
        $metric2 = new Metric('test_2', 2.0, ['common' => 'tag']);
        $source = new IterableMetricSource([$metric1, $metric2]);
        $collector = $this->createMock(MetricCollectorInterface::class);
        $collector->expects($this->once())->method('collect')->willReturn($source);

        $decorator = new TaggingCollectorDecorator($collector, ['extra' => 'custom']);
        foreach ($decorator->collect()->getMetrics() as $metric) {
            self::assertArrayHasKey('extra', $metric->getTags());
            self::assertSame('custom', $metric->getTags()['extra']);
        }
    }
}
