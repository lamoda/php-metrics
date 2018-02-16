<?php

namespace Lamoda\MetricInfra\Tests\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Lamoda\MetricInfra\Doctrine\AbstractDoctrineMetricStorage;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;
use PHPUnit\Framework\TestCase;

final class AbstractDoctrineMetricStorageTest extends TestCase
{
    public function testDecoratorCreatesNewMetricOnDemand()
    {
        $key = 'new_metric';

        $manager = $this->createMock(ObjectManager::class);
        $delegate = $this->createMock(AdjustableMetricStorageInterface::class);

        /** @var AdjustableMetricStorageInterface|\PHPUnit_Framework_MockObject_MockObject $decorator */
        $decorator = $this->getMockBuilder(AbstractDoctrineMetricStorage::class)
            ->setConstructorArgs([$delegate, $manager])
            ->setMethods(['instantiateEmptyMetric'])
            ->getMock();

        $metric = $this->createMock(AdjustableMetricInterface::class);

        $decorator->expects($this->once())->method('instantiateEmptyMetric')->willReturn($metric);
        $manager->expects($this->once())->method('persist')->with($metric);

        $delegate->expects($this->once())->method('hasAdjustableMetric');
        $delegate->expects($this->never())->method('getAdjustableMetric');

        self::assertTrue($decorator->hasAdjustableMetric($key));
        self::assertSame($metric, $decorator->getAdjustableMetric($key));
    }

    public function testDecoratorReturnsExistingMetric()
    {
        $key = 'existing_metric';

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->never())->method('persist');

        $delegate = $this->createMock(AdjustableMetricStorageInterface::class);

        /** @var AdjustableMetricStorageInterface|\PHPUnit_Framework_MockObject_MockObject $decorator */
        $decorator = $this->getMockBuilder(AbstractDoctrineMetricStorage::class)
            ->setConstructorArgs([$delegate, $manager])
            ->setMethods(['instantiateEmptyMetric'])
            ->getMock();

        $decorator->expects($this->never())->method('instantiateEmptyMetric');

        $metric = $this->createMock(AdjustableMetricInterface::class);

        $delegate->expects($this->once())->method('hasAdjustableMetric')->willReturn(true);
        $delegate->expects($this->once())->method('getAdjustableMetric')->willReturn($metric);

        self::assertTrue($decorator->hasAdjustableMetric($key));
        self::assertSame($metric, $decorator->getAdjustableMetric($key));
    }
}
