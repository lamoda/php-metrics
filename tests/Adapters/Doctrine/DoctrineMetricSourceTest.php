<?php

namespace Lamoda\Metric\Adapters\Tests\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Adapters\Doctrine\DoctrineMetricSource;
use Lamoda\Metric\Common\Metric;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @covers \Lamoda\Metric\Adapters\Doctrine\DoctrineMetricSource
 */
final class DoctrineMetricSourceTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownEntityThrowsException()
    {
        /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn(null);

        new DoctrineMetricSource($registry, 'Some\Unknown\Class');
    }

    public function testIterating()
    {
        $class = 'Some\Metric\Class';

        $m1 = new Metric('test_1', 1);
        $m2 = new Metric('test_2', 2);
        $m3 = new Metric('test_3', 3);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('findAll')->willReturn([$m1, $m2, $m3]);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('getRepository')->with($class)->willReturn($repository);

        /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with($class)->willReturn($manager);

        $source = new DoctrineMetricSource($registry, $class);

        self::assertSame([$m1, $m2, $m3], array_values(iterator_to_array($source)));
    }
}
