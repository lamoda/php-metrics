<?php

namespace Lamoda\MetricInfra\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Lamoda\MetricStorage\AdjustableMetricInterface;
use Lamoda\MetricStorage\AdjustableMetricStorageInterface;

abstract class AbstractDoctrineMetricStorage implements AdjustableMetricStorageInterface
{
    /**
     * @var AdjustableMetricStorageInterface
     */
    private $delegate;
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * AbstractDoctrineMetricStorage constructor.
     *
     * @param AdjustableMetricStorageInterface $delegate
     * @param ObjectManager                    $manager
     */
    public function __construct(AdjustableMetricStorageInterface $delegate, ObjectManager $manager)
    {
        $this->delegate = $delegate;
        $this->manager = $manager;
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key): bool
    {
        return true;
    }

    /** {@inheritdoc} */
    public function getAdjustableMetric(string $key): AdjustableMetricInterface
    {
        if ($this->delegate->hasAdjustableMetric($key)) {
            return $this->delegate->getAdjustableMetric($key);
        }

        return $this->createAndPersistEmptyMetric($key);
    }

    abstract protected function instantiateEmptyMetric(string $key): AdjustableMetricInterface;

    private function createAndPersistEmptyMetric(string $key): AdjustableMetricInterface
    {
        $metric = $this->instantiateEmptyMetric($key);

        $this->manager->persist($metric);

        return $metric;
    }
}
