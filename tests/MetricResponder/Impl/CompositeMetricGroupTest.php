<?php

namespace Lamoda\MetricResponder\Tests\Impl;

use Lamoda\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\MetricBundle\Tests\Fixtures\Entity\MetricGroup;
use Lamoda\MetricResponder\MetricGroup\MergingMetricGroup;
use PHPUnit\Framework\TestCase;

final class CompositeMetricGroupTest extends TestCase
{
    public function testCompositionGenerator()
    {
        $m1 = new Metric('m1', 1);
        $m2 = new Metric('m2', 2);
        $m3 = new Metric('m3', 3);

        $g1 = new MetricGroup('g1', [$m1, $m2], ['t1' => '1', 't2' => '1']);
        $g2 = new MetricGroup('g2', [$m2, $m3], ['t2' => '2', 't3' => '2']);

        $group = new MergingMetricGroup('composite', [$g1, $g2]);

        self::assertSame('composite', $group->getName());
        self::assertSame([$m1, $m2, $m2, $m3], iterator_to_array($group, false));
        self::assertSame(['t1' => '1', 't2' => '2', 't3' => '2'], $group->getTags());
    }
}
