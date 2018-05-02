<?php

namespace Lamoda\Metric\MetricBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;

/**
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\LamodaMetricExtension
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Configuration
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterCollectorsPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterReceiversPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterResponseFactoriesPass
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Storage
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Responder
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\ResponseFactory
 * @covers \Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Source
 * @runTestsInSeparateProcesses
 */
final class MetricRespondingTest extends AbstractMetricBundleTest
{
    public function getTelegrafTestRoutes(): array
    {
        return [
            'basic' => ['/metrics/telegraf_json'],
            'custom' => ['/metrics/custom_telegraf'],
        ];
    }

    /**
     * @param string $path
     *
     * @dataProvider getTelegrafTestRoutes
     */
    public function testTelegrafMetricsReturned(string $path): void
    {
        $container = static::getContainer();
        $this->persistMetrics($container);
        static::$client->request('GET', $path);
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
{
    "custom_metric": 1,
    "custom_metric_for_composite": 2.2
}
JSON
            ,
            $response->getContent()
        );
    }

    public function testPrometheusMetricsReturned(): void
    {
        $container = static::getContainer();
        $this->persistMetrics($container);
        static::$client->request('GET', '/metrics/prometheus');
        $response = static::$client->getResponse();
        self::assertNotNull($response);
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isCacheable());

        self::assertSame(
            <<<'PROMETHEUS'
metrics_custom_metric{collector="raw"} 1
metrics_custom_metric_for_composite{collector="raw"} 2.2

PROMETHEUS
            ,
            $response->getContent()
        );
    }

    /**
     * @param $container
     *
     * @return EntityManagerInterface
     */
    private function persistMetrics($container): EntityManagerInterface
    {
        /** @var EntityManagerInterface $doctrine */
        $doctrine = $container->get('doctrine.orm.entity_manager');

        $m1 = new Metric('test_1', 241.0, ['own_tag' => 'm1']);
        $m2 = new Metric('test_2', 12.3, ['own_tag' => 'm2']);
        $m3 = new Metric('test_3', 17.0, ['own_tag' => 'm3']);
        $m4 = new Metric('test_4', 5.5, ['own_tag' => 'm4']);
        $doctrine->persist($m1);
        $doctrine->persist($m3);
        $doctrine->persist($m4);
        $doctrine->persist($m2);

        $doctrine->flush();
        $doctrine->clear();

        return $doctrine;
    }
}
