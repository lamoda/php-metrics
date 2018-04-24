<?php

namespace Lamoda\Metric\Adapters\Tests\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Adapters\Doctrine\AtomicMutableWrapper;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\Metric\Storage\MetricDriverInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;

final class MetricDriver implements \IteratorAggregate, MetricDriverInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** {@inheritdoc} */
    public function createMetric(string $name, float $value, array $tags = []): MutableMetricInterface
    {
        $metric = new Metric($name, $value, $tags);
        $this->entityManager->persist($metric);

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    /** {@inheritdoc} */
    public function findMetric(string $name, array $tags = []): ?MutableMetricInterface
    {
        /** @var MutableMetricInterface $metric */
        $metric = $this->getRepository()->findOneBy(['name' => $name, 'tags' => json_encode($tags)]);

        if (!$metric) {
            return null;
        }

        return new AtomicMutableWrapper($this->entityManager, $metric);
    }

    /** {@inheritdoc} */
    public function getMetrics(): \Traversable
    {
        return new \ArrayIterator($this->getRepository()->findAll());
    }

    /** {@inheritdoc} */
    public function getIterator(): \Traversable
    {
        return $this->getMetrics();
    }

    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Metric::class);
    }
}
