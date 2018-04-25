<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\Exception\ReceiverException;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;

abstract class AbstractDoctrineStorage implements \IteratorAggregate, MetricStorageInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        $this->entityManager = $this->getEntityManager($registry);
    }

    final public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    /** {@inheritdoc} */
    final public function receive(MetricSourceInterface $source): void
    {
        $this->entityManager->beginTransaction();
        try {
            $this->doReceive($source);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw ReceiverException::becauseOfStorageFailure($exception);
        }
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        foreach ($this->createMetricQueryBuilder('metrics')->getQuery()->iterate() as $row) {
            yield $row[0];
        }
    }

    /** {@inheritdoc} */
    public function findMetric(string $name, array $tags = []): ?MutableMetricInterface
    {
        $metric = $this->doFindMetric($name, $tags);
        if (!$metric) {
            return null;
        }

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    /** {@inheritdoc} */
    public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface
    {
        $metric = $this->doCreateMetric($name, $value, $tags);
        $this->entityManager->persist($metric);

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    abstract protected function doFindMetric(string $name, array $tags = []): ?MutableMetricInterface;

    abstract protected function doCreateMetric(string $name, float $value, array $tags = []): MutableMetricInterface;

    abstract protected function getEntityManager(ManagerRegistry $registry): EntityManagerInterface;

    abstract protected function createMetricQueryBuilder(string $alias): QueryBuilder;

    /**
     * @param MetricSourceInterface $source
     */
    protected function doReceive(MetricSourceInterface $source): void
    {
        foreach ($source->getMetrics() as $metric) {
            $tags = $metric->getTags();
            $name = $metric->getName();
            $value = $metric->resolve();
            $resolved = $this->findMetric($name, $tags);
            if (!$resolved) {
                $resolved = $this->createMetric($name, 0, $tags);
            }
            $resolved->setValue($value);
        }
    }
}
