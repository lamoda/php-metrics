<?php

namespace Lamoda\MetricResponder\Tests\GroupSource;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\MetricBundle\Tests\Fixtures\Entity\MetricGroup;
use Lamoda\MetricResponder\GroupSource\DoctrineMetricGroupSource;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;

final class DoctrineMetricGroupSourceTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownEntityThrowsException()
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn(null);

        new DoctrineMetricGroupSource($registry, 'Some\Unknown\Class');
    }

    public function testIterating()
    {
        $class = 'Some\Group\Class';

        $g1 = new MetricGroup('g1');
        $g2 = new MetricGroup('g2');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('findAll')->willReturn([$g1, $g2]);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('getRepository')->with($class)->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with($class)->willReturn($manager);

        $source = new DoctrineMetricGroupSource($registry, $class);

        self::assertSame([$g1, $g2], array_values(iterator_to_array($source)));
    }
}
