<?php

namespace Lamoda\MetricBundle\Tests\Entity;

use Lamoda\MetricBundle\Entity\MetricGroup;
use PHPUnit\Framework\TestCase;

final class MetricGroupTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMetricGroupConstructorThrowsAnException()
    {
        $this->getMockBuilder(MetricGroup::class)
            ->setConstructorArgs(
                [
                    'test',
                    'not an array',
                ]
            )->getMock();
    }
}
