<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;

final class SingleSourceCollector implements MetricCollectorInterface
{
    /** @var MetricSourceInterface */
    private $source;

    /**
     * @param MetricSourceInterface $source
     */
    public function __construct(MetricSourceInterface $source)
    {
        $this->source = $source;
    }

    /** {@inheritdoc} */
    public function collect(): MetricSourceInterface
    {
        return $this->source;
    }
}
