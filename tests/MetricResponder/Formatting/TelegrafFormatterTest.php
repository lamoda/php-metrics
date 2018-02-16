<?php

namespace Lamoda\MetricResponder\Tests\Formatting;

use Lamoda\MetricResponder\GroupSource\ArrayMetricGroupSource;
use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Lamoda\MetricResponder\MetricImpl\Metric;
use Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory;
use PHPUnit\Framework\TestCase;

final class TelegrafFormatterTest extends TestCase
{
    public function testSimpleFormatting()
    {
        $group = new CombinedMetricGroup('test', ['tag1' => 'test1', 'tag2' => 'test2']);

        $group->addMetric(new Metric('m1', 1));
        $group->addMetric(new Metric('m2', 2));

        $formatter = new TelegrafResponseFactory();

        self::assertJsonStringEqualsJsonString(
            json_encode(
                [
                    [
                        'm1' => 1.0,
                        'm2' => 2.0,
                        'tag1' => 'test1',
                        'tag2' => 'test2',
                    ],
                ]
            ),
            (string) $formatter->create(new ArrayMetricGroupSource([$group]))->getBody()
        );
    }

    public function testEmptyGroupIsNotRendered()
    {
        $group1 = new CombinedMetricGroup('test', ['tag1' => 'test1', 'tag2' => 'test2']);

        $group1->addMetric(new Metric('m1', 1));
        $group1->addMetric(new Metric('m2', 2));

        $group2 = new CombinedMetricGroup('test', ['tag1' => 'test1', 'tag2' => 'test2']);

        $formatter = new TelegrafResponseFactory();

        self::assertJsonStringEqualsJsonString(
            json_encode(
                [
                    [
                        'm1' => 1.0,
                        'm2' => 2.0,
                        'tag1' => 'test1',
                        'tag2' => 'test2',
                    ],
                ]
            ),
            (string) $formatter->create(new ArrayMetricGroupSource([$group1, $group2]))->getBody()
        );
    }
}
