<?php

namespace Lamoda\Metric\Adapters\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Adapters\Doctrine\AtomicMutableWrapper;
use Lamoda\Metric\Storage\MutableMetricInterface;
use PHPUnit\Framework\TestCase;

final class AtomicMutableWrapperTest extends TestCase
{
    public function testGenericMethodsAreProxies(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $metric = $this->createMock(MutableMetricInterface::class);
        $value = 241.0;
        $metric->expects($this->once())->method('resolve')->willReturn($value);

        $name = 'test_metric';
        $metric->expects($this->once())->method('getName')->willReturn($name);

        $tags = ['tag' => 'v1'];
        $metric->expects($this->once())->method('getTags')->willReturn($tags);

        $wrapper = new AtomicMutableWrapper($em, $metric);
        self::assertSame($value, $wrapper->resolve());
        self::assertSame($name, $wrapper->getName());
        self::assertSame($tags, $wrapper->getTags());
    }

    public function testSettingIsAtomicOperation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $metric = $this->createMock(MutableMetricInterface::class);

        $em->expects($this->once())->method('transactional')->willReturnCallback(
            function (callable $callback) {
                return $callback();
            }
        );
        $em->expects($this->once())->method('lock')->with($metric);
        $em->expects($this->once())->method('refresh')->with($metric);
        $em->expects($this->once())->method('flush');

        $metric->expects($this->once())->method('setValue')->with(10);

        $wrapper = new AtomicMutableWrapper($em, $metric);
        $wrapper->setValue(10);
    }

    public function testAdjustingIsAtomicOperation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $metric = $this->createMock(MutableMetricInterface::class);

        $em->expects($this->once())->method('transactional')->willReturnCallback(
            function (callable $callback) {
                return $callback();
            }
        );
        $em->expects($this->once())->method('lock')->with($metric);
        $em->expects($this->once())->method('refresh')->with($metric);
        $em->expects($this->once())->method('flush');

        $metric->expects($this->once())->method('adjust')->with(10);

        $wrapper = new AtomicMutableWrapper($em, $metric);
        $wrapper->adjust(10);
    }
}
