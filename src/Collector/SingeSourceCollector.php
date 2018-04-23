<?php

namespace Lamoda\Metric\Collector;

use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Common\Source\ExtraTagsMetricSource;

final class SingeSourceCollector implements MetricCollectorInterface
{
    /** @var MetricSourceInterface */
    private $source;

    /**
     * @param MetricSourceInterface $source
     * @param string[]              $tags
     */
    public function __construct(MetricSourceInterface $source, array $tags = [])
    {
        $this->source = new ExtraTagsMetricSource($source, $tags);
    }

    /** {@inheritdoc} */
    public function collect(): MetricSourceInterface
    {
        return $this->source;
    }
}
