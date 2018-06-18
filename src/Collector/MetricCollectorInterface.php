<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;

interface MetricCollectorInterface
{
    /**
     * Collect metrics into metric source.
     *
     * @return MetricSourceInterface
     */
    public function collect(): MetricSourceInterface;
}
