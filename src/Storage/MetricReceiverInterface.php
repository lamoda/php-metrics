<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Storage\Exception\ReceiverException;

interface MetricReceiverInterface
{
    /**
     * Receive metric source into storage.
     *
     * @param MetricSourceInterface $source
     *
     * @throws ReceiverException
     */
    public function receive(MetricSourceInterface $source): void;
}
