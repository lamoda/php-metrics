<?php

namespace Lamoda\Metric\MetricBundle\Tests\Fixtures\Storage;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Lamoda\Metric\Adapters\Doctrine\AbstractDoctrineStorage;
use Lamoda\Metric\MetricBundle\Tests\Fixtures\Entity\Metric;
use Lamoda\Metric\Storage\MutableMetricInterface;

final class MetricStorage extends AbstractDoctrineStorage
{
    protected function doFindMetric(string $name, array $tags = []): ?MutableMetricInterface
    {
        ksort($tags);

        return $this->createMetricQueryBuilder('metric')
            ->andWhere('metric.name = :name')
            ->setParameter('name', $name)
            ->andWhere('metric.tags = :tags')
            ->setParameter('tags', serialize($tags))->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    protected function doCreateMetric(string $name, float $value, array $tags = []): MutableMetricInterface
    {
        return new Metric($name, $value, $tags);
    }

    protected function createMetricQueryBuilder(string $alias): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()->select($alias)->from(Metric::class, $alias);
    }

    protected function getEntityManager(ManagerRegistry $registry): EntityManagerInterface
    {
        return $registry->getManagerForClass(Metric::class);
    }
}
