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
    public function testNormalize()
    {
        $source = new IterableMetricSource(
            [
                new Metric('metrics_orders', 200.0, ['country' => 'ru']),
                new Metric('metrics_errors', 0.0, ['country' => 'ru']),
            ]
        );

        $factory = new TelegrafJsonResponseFactory();
        $response = $factory->create(
            $source,
            [
                'propagate_tags' => ['country'],
                'group_by_tag' => 'country',
            ]
        );
        $data = (string) $response->getBody();
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'metrics_orders' => 200.0,
                    'metrics_errors' => 0.0,
                    'country' => 'ru',
                ]
            ),
            $data
        );
    }
}
