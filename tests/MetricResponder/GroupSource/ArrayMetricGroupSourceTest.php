<?php

namespace Lamoda\MetricResponder\Tests\GroupSource;

use Lamoda\MetricResponder\GroupSource\ArrayMetricGroupSource;
use Lamoda\MetricResponder\MetricGroupInterface;
use PHPUnit\Framework\TestCase;

final class ArrayMetricGroupSourceTest extends TestCase
{
    public function testIteratingMethod()
    {
        $groups = [
            $this->createMock(MetricGroupInterface::class),
            $this->createMock(MetricGroupInterface::class),
            $this->createMock(MetricGroupInterface::class),
        ];

        $source = new ArrayMetricGroupSource($groups);

        self::assertSame($groups, iterator_to_array($source));
        self::assertSame($groups, iterator_to_array($source->all()));
    }
}
