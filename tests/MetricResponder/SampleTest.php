<?php

namespace Lamoda\MetricResponder\Tests;

use Lamoda\MetricResponder\GroupSource\MergingMetricGroupSource;
use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Lamoda\MetricResponder\MetricImpl\Metric;
use Lamoda\MetricResponder\ResponseFactory\TelegrafResponseFactory;
use PHPUnit\Framework\TestCase;

final class SampleTest extends TestCase
{
    public function testSampleResultFormatting()
    {
        $metric = new Metric('test', 1);

        $group = new CombinedMetricGroup('metric_group_1', ['tag1' => 'tag_value']);
        $group->addMetric($metric);

        $source = new MergingMetricGroupSource();
        $source->register($group);

        $formatter = new TelegrafResponseFactory();

        $expected = json_encode(
            [
                [
                    'test' => 1.0,
                    'tag1' => 'tag_value',
                ],
            ]
        );

        self::assertJsonStringEqualsJsonString(
            $expected,
            (string) $formatter->create($source)->getBody()
        );
    }
}
