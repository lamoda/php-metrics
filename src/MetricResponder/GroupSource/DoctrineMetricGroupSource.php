<?php

namespace Lamoda\MetricResponder\GroupSource;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

final class DoctrineMetricGroupSource implements \IteratorAggregate, MetricGroupSourceInterface
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
    public function __construct(ManagerRegistry $registry, string $class = MetricGroupInterface::class)
    {
        $this->registry = $registry;
        $this->class = $class;

        if (null === $this->registry->getManagerForClass($this->class)) {
            throw new \InvalidArgumentException(sprintf('Manager for "%s" entity is not found', $this->class));
        }
    }

    /** {@inheritdoc} */
    public function all(): \Traversable
    {
        return new \ArrayIterator($this->getManager()->getRepository($this->class)->findAll());
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        return $this->all();
    }

    private function getManager(): EntityManagerInterface
    {
        return $this->registry->getManagerForClass($this->class);
    }
}
