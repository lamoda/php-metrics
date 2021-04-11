<?php

namespace Lamoda\Metric\Common\Tests\Source;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\Source\DefaultTaggingMetricSource;
use Lamoda\Metric\Tests\Builders\TraversableMetricSourceBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Common\Source\DefaultTaggingMetricSource
 */
final class DefaultTaggingMetricSourceTest extends TestCase
{
    public function testSourceProxiesMetrics(): void
    {
        $m1 = new Metric('test_1', 1.0, ['common' => 'value']);
        $m2 = new Metric('test_2', 2.0, ['common' => 'value']);
        $inner = TraversableMetricSourceBuilder::build([$m1, $m2]);

        $source = new DefaultTaggingMetricSource($inner);
        $metrics = iterator_to_array($source);
        self::assertCount(2, $metrics);
        $expected = [
            'test_1' => 1.0,
            'test_2' => 2.0,
        ];
        $actual = [];
        foreach ($metrics as $metric) {
            $actual[$metric->getName()] = $metric->resolve();
        }
        self::assertSame($expected, $actual);
    }

    public function testSourceAddsExtraTags(): void
    {
        $m1 = new Metric('test_1', 1.0);
        $m2 = new Metric('test_2', 2.0);
        $inner = TraversableMetricSourceBuilder::build([$m1, $m2]);

        $source = new DefaultTaggingMetricSource($inner, ['extra' => 'value']);
        /** @var MetricInterface[] $metrics */
        $metrics = iterator_to_array($source);
        foreach ($metrics as $metric) {
            self::assertArrayHasKey('extra', $metric->getTags());
            self::assertSame('value', $metric->getTags()['extra']);
        }
    }

    public function testSourceDoesNotOverrideTags(): void
    {
        $m1 = new Metric('test_1', 1.0, ['tag' => 'value']);
        $inner = TraversableMetricSourceBuilder::build([$m1]);

        $source = new DefaultTaggingMetricSource($inner, ['tag' => 'new_value']);
        foreach ($source as $metric) {
            /** @var MetricInterface $metric */
            self::assertArrayHasKey('tag', $metric->getTags());
            self::assertSame('value', $metric->getTags()['tag']);
        }
    }
}
