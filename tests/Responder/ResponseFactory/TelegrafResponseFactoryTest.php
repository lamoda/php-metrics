<?php

namespace Lamoda\Metric\Responder\Tests\ResponseFactory;

use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Responder\ResponseFactory\TelegrafJsonResponseFactory
 */
final class TelegrafResponseFactoryTest extends TestCase
{
    public function testResponseFormat(): void
    {
        $source = new IterableMetricSource(
            [
                new Metric('metrics_orders', 200.0, ['country' => 'ru']),
                new Metric('metrics_errors', 0.0, ['country' => 'ru']),
                new Metric('untagged_metric', 5.0),
            ]
        );

        $factory = new TelegrafJsonResponseFactory();
        $response = $factory->create(
            $source,
            [
                'propagate_tags' => ['country'],
                'group_by_tags' => ['country'],
            ]
        );
        self::assertContains('application/json', $response->getHeaderLine('Content-Type'));

        $data = (string) $response->getBody();
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    [
                        'metrics_orders' => 200.0,
                        'metrics_errors' => 0.0,
                        'country' => 'ru',
                    ],
                    [
                        'untagged_metric' => 5.0,
                    ],
                ]
            ),
            $data
        );
    }

    public function testSingleGroupIsNotFormattedAsSingleElementArray(): void
    {
        $source = new IterableMetricSource(
            [
                new Metric('untagged_metric', 5.0),
            ]
        );

        $factory = new TelegrafJsonResponseFactory();
        $response = $factory->create($source);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['untagged_metric' => 5.0]),
            (string) $response->getBody()
        );
    }
}
