<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\Exception\ReceiverException;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;

abstract class AbstractDoctrineStorage implements \IteratorAggregate, MetricStorageInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
    final public function findMetric(string $name, array $tags = []): ?MutableMetricInterface
    {
        $metric = $this->doFindMetric($name, $tags);
        if (!$metric) {
            return null;
        }

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    /** {@inheritdoc} */
    final public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface
    {
        $metric = $this->doCreateMetric($name, $value, $tags);
        $this->entityManager->persist($metric);

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    abstract protected function doFindMetric(string $name, array $tags = []): ?MutableMetricInterface;

    abstract protected function doCreateMetric(string $name, float $value, array $tags = []): MutableMetricInterface;

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
