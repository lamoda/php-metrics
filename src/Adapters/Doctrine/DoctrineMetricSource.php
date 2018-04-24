<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\AdjustableMetricInterface;
use Lamoda\Metric\Storage\AdjustableMetricStorageInterface;
use Lamoda\Metric\Storage\Exception\MetricStorageException;

final class DoctrineMetricSource implements \IteratorAggregate, MetricSourceInterface
{
    /** @var ManagerRegistry */
    private $registry;
    /** @var string */
    private $class;

    /**
     * @param ManagerRegistry $registry
     * @param string          $class
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ManagerRegistry $registry, string $class = MetricInterface::class)
    {
        $this->registry = $registry;
        $this->class = $class;

        if (null === $registry->getManagerForClass($this->class)) {
            throw new \InvalidArgumentException(sprintf('Manager for "%s" entity is not found', $this->class));
        }
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        return new \ArrayIterator($this->getManager()->getRepository($this->class)->findAll());
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    private function getManager(): EntityManagerInterface
    {
        return $this->registry->getManagerForClass($this->class);
    }
}
