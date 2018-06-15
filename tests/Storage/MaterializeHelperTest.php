<?php

namespace Lamoda\Metric\Storage\Tests;

use Lamoda\Metric\Collector\CollectorRegistry;
use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\MaterializeHelper;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Storage\StorageRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Storage\MaterializeHelper
 */
final class MaterializeHelperTest extends TestCase
{
    public function testMaterializingMetrics(): void
    {
        $collectorRegistry = new CollectorRegistry();
        $storageRegistry = new StorageRegistry();

        $helper = new MaterializeHelper($collectorRegistry, $storageRegistry);

        $collector = $this->createMock(MetricCollectorInterface::class);
        $collectorRegistry->register('collector', $collector);

        $storage = $this->createMock(MetricStorageInterface::class);
        $storageRegistry->register('storage', $storage);

        $m1 = $this->createMock(MetricInterface::class);
        $m2 = $this->createMock(MetricInterface::class);
        $source = $this->createMock(MetricSourceInterface::class);
        $source->expects($this->once())->method('getMetrics')->willReturn(new \ArrayIterator([$m1, $m2]));

        $collector->expects($this->once())->method('collect')->willReturn($source);
        $storage->expects($this->once())->method('receive')->with(
            $this->callback(
                function (MetricSourceInterface $metricSource) use ($m1, $m2) {
                    $actual = [];
                    foreach ($metricSource->getMetrics() as $metric) {
                        $actual[$metric->getName()] = ['value' => $metric->resolve(), 'tags' => $metric->getTags()];
                    }
                    $expected = [
                        $m1->getName() => ['value' => $m1->resolve(), 'tags' => $m1->getTags()],
                        $m2->getName() => ['value' => $m2->resolve(), 'tags' => $m2->getTags()],
                    ];
                    $this->assertEquals($expected, $actual);

                    return true;
                }
            )
        );

        $helper->materialize('collector', 'storage');
    }
}
