<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Common\MetricSourceInterface;

interface MetricReceiverInterface
{
    /**
     * Receive metric source into storage.
     *
     * @param MetricSourceInterface $source
     */
    public function receive(MetricSourceInterface $source);
}
