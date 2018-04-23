<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Common\Factory\MetricFactoryInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\Exception\ReceiverException;
use Lamoda\Metric\Storage\MetricReceiverInterface;

final class DoctrineReceiver implements MetricReceiverInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MetricFactoryInterface */
    private $metricFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MetricFactoryInterface $metricFactory
    ) {
        $this->entityManager = $entityManager;
        $this->metricFactory = $metricFactory;
    }

    /** {@inheritdoc} */
    public function receive(MetricSourceInterface $source)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($source->getMetrics() as $metric) {
                $persistentMetric = $this->metricFactory->createMetric($metric->getName(), $metric->resolve());
                $this->entityManager->persist($persistentMetric);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw ReceiverException::becauseOfStorageFailure($exception);
        }
    }
}
