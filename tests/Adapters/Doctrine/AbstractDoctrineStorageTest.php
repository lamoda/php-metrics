<?php

namespace Lamoda\Metric\Adapters\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Adapters\Doctrine\AbstractDoctrineStorage;
use Lamoda\Metric\Common\Metric;
use Lamoda\Metric\Common\MetricSourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Metric\Adapters\Doctrine\AbstractDoctrineStorage
 * @covers \Lamoda\Metric\Storage\Exception\ReceiverException
 */
final class AbstractDoctrineStorageTest extends TestCase
{
    public function testReceivingPersistsNewMetrics(): void
    {
        $knownSourceMetric = new Metric('known_metric', 1.0, ['tag' => 'v1']);
        $unknownSourceMetric = new Metric('unknown_metric', 241.0, ['tag' => 'v1']);
        $createdMetric = new Metric($unknownSourceMetric->getName(), 0, $unknownSourceMetric->getTags());

        $metrics = new \ArrayIterator(
            [
                $knownSourceMetric,
                $unknownSourceMetric,
            ]
        );

        $source = $this->getMockBuilder([\IteratorAggregate::class, MetricSourceInterface::class])->getMock();
        $source->method('getMetrics')->willReturn($metrics);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('commit');
        $em->expects($this->never())->method('rollback');
        $em->expects($this->once())->method('persist')->with(
            $this->callback(
                function ($metric) use ($unknownSourceMetric, $createdMetric) {
                    self::assertNotSame($metric, $unknownSourceMetric);
                    self::assertEquals($metric, $createdMetric);

                    return true;
                }
            )
        );

        $storage = $this->getMockBuilder(AbstractDoctrineStorage::class)
            ->setConstructorArgs([$em])
            ->setMethods(['doFindMetric', 'doCreateMetric', 'getMetrics'])
            ->getMock();

        $storage->expects($this->exactly(2))->method('doFindMetric')
            ->willReturnMap(
                [
                    [$knownSourceMetric->getName(), $knownSourceMetric->getTags(), clone $knownSourceMetric],
                    [$unknownSourceMetric->getName(), $unknownSourceMetric->getTags(), null],
                ]
            );

        $storage->expects($this->once())->method('doCreateMetric')
            ->with($unknownSourceMetric->getName(), 0, $unknownSourceMetric->getTags())
            ->willReturn($createdMetric);

        $storage->receive($source);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionCallsRollback(): void
    {
        $source = $this->getMockBuilder([\IteratorAggregate::class, MetricSourceInterface::class])->getMock();
        $source->method('getMetrics')->willReturn(new \ArrayIterator([new Metric('test', 0)]));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->never())->method('flush');
        $em->expects($this->never())->method('commit');
        $em->expects($this->once())->method('rollback');

        $storage = $this->getMockBuilder(AbstractDoctrineStorage::class)
            ->setConstructorArgs([$em])
            ->setMethods(['doFindMetric', 'doCreateMetric', 'getMetrics'])
            ->getMock();

        $storage->expects($this->once())->method('doFindMetric')->willThrowException(new \RuntimeException());

        $storage->receive($source);
    }

    public function testIteratorIsGetMetricsProxy(): void
    {
        $storage = $this->getMockBuilder(AbstractDoctrineStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['doFindMetric', 'doCreateMetric', 'getMetrics'])
            ->getMock();

        $expected = new \ArrayIterator([]);

        $storage->expects($this->once())->method('getMetrics')->willReturn($expected);
        self::assertSame($expected, $storage->getIterator());
    }
}
