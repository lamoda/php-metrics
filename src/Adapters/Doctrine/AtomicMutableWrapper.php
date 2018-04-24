<?php

namespace Lamoda\Metric\Adapters\Doctrine;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\Metric\Storage\MutableMetricInterface;

final class AtomicMutableWrapper implements MutableMetricInterface
{
    /** @var EntityManagerInterface */
    private $manager;
    /** @var MutableMetricInterface */
    private $metric;

    public function __construct(EntityManagerInterface $manager, MutableMetricInterface $metric)
    {
        $this->manager = $manager;
        $this->metric = $metric;
    }

    /** {@inheritdoc} */
    public function adjust(float $delta): void
    {
        $this->manager->transactional(
            function () use ($delta) {
                $this->manager->lock($this->metric, LockMode::PESSIMISTIC_WRITE);

                $this->manager->refresh($this->metric);

                $this->metric->adjust($delta);

                $this->manager->flush();
            }
        );
    }

    /** {@inheritdoc} */
    public function setValue(float $delta): void
    {
        $this->manager->transactional(
            function () use ($delta) {
                $this->manager->lock($this->metric, LockMode::PESSIMISTIC_WRITE);

                $this->manager->refresh($this->metric);

                $this->metric->setValue($delta);

                $this->manager->flush();
            }
        );
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->metric->getName();
    }

    /** {@inheritdoc} */
    public function resolve(): float
    {
        return $this->metric->resolve();
    }

    /** {@inheritdoc} */
    public function getTags(): array
    {
        return $this->metric->getTags();
    }
}
