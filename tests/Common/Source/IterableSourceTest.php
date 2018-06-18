<?php

namespace Lamoda\Metric\Common\Tests\Source;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Common\Source\IterableMetricSource
 */
final class IterableSourceTest extends TestCase
{
    public function testIteratingArray(): void
    {
        $m1 = new Metric('test_1', 1.0);
        $m2 = new Metric('test_2', 2.0);
        $array = [$m1, $m2];

        $source = new IterableMetricSource($array);

        $metrics = $source->getMetrics();
        self::assertSame($metrics, $source->getIterator());
        self::assertSame($array, iterator_to_array($metrics));
    }

    public function testIteratingTraversable(): void
    {
        $m1 = new Metric('test_1', 1.0);
        $m2 = new Metric('test_2', 2.0);
        $array = [$m1, $m2];

        $source = new IterableMetricSource(new \ArrayIterator($array));

        $metrics = $source->getMetrics();
        self::assertSame($metrics, $source->getIterator());
        self::assertSame($array, iterator_to_array($metrics));
    }
}
