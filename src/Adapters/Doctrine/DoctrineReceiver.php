<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\Exception\ReceiverException;
use Lamoda\Metric\Storage\MetricDriverInterface;
use Lamoda\Metric\Storage\MetricReceiverInterface;

final class DoctrineReceiver implements MetricReceiverInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MetricDriverInterface */
    private $driver;

    public function __construct(EntityManagerInterface $entityManager, MetricDriverInterface $driver)
    {
        $this->entityManager = $entityManager;
        $this->driver = $driver;
    }

    /** {@inheritdoc} */
    public function receive(MetricSourceInterface $source): void
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($source->getMetrics() as $metric) {
                $tags = $metric->getTags();
                $name = $metric->getName();
                $value = $metric->resolve();
                $resolved = $this->driver->findMetric($name, $tags);
                if (!$resolved) {
                    $this->driver->createMetric($name, $value, $tags);
                } else {
                    $resolved->setValue($value);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw ReceiverException::becauseOfStorageFailure($exception);
        }
    }
}
