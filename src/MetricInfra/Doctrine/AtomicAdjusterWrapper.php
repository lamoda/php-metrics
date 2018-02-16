<?php

namespace Lamoda\MetricInfra\Doctrine;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Lamoda\MetricStorage\AdjustableMetricInterface;

final class AtomicAdjusterWrapper implements AdjustableMetricInterface
{
    /** @var EntityManagerInterface */
    private $manager;
    /** @var AdjustableMetricInterface */
    private $metric;

    public function __construct(EntityManagerInterface $manager, AdjustableMetricInterface $metric)
    {
        $this->manager = $manager;
        $this->metric = $metric;
    }

    /** {@inheritdoc} */
    public function adjust(float $delta)
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
}
