<?php

namespace Lamoda\Metric\MetricBundle\Tests;

use Lamoda\Metric\Storage\MetricMutatorInterface;
use Lamoda\Metric\Storage\MetricStorageInterface;

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
final class StoredMetricMutatorTest extends AbstractMetricBundleTestClass
{
    public function testAdjustableMetrics(): void
    {
        /** @var MetricMutatorInterface $adjuster */
        $adjuster = self::getContainer()->get('test.' . MetricMutatorInterface::class);
        /** @var MetricStorageInterface $storage */
        $storage = self::getContainer()->get('test.doctrine_metric_storage');

        $adjuster->adjustMetricValue(10, 'test_1', ['tag' => 'value1']);
        $adjuster->adjustMetricValue(20, 'test_1', ['tag' => 'value2']);

        $metric1 = $storage->findMetric('test_1', ['tag' => 'value1']);
        $metric2 = $storage->findMetric('test_1', ['tag' => 'value2']);
        self::assertNotNull($metric1);
        self::assertNotNull($metric2);

        self::assertNotSame($metric1, $metric2);
        self::assertEquals(10, $metric1->resolve());
        self::assertEquals(20, $metric2->resolve());

        $adjuster->adjustMetricValue(30, 'test_1', ['tag' => 'value1']);
        $metric3 = $storage->findMetric('test_1', ['tag' => 'value1']);
        self::assertNotNull($metric3);
        self::assertEquals($metric1, $metric3);
        self::assertEquals(40, $metric3->resolve());
    }
}
