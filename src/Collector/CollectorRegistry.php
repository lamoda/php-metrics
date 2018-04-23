<?php

namespace Lamoda\Metric\Collector;

/** {@inheritdoc} */
final class CollectorRegistry
{
    /** @var MetricCollectorInterface[] */
    private $collectors = [];

    /**
     * Add collector to registry.
     *
     * @param string                   $name
     * @param MetricCollectorInterface $collector
     */
    public function register(string $name, MetricCollectorInterface $collector)
    {
        $this->collectors[$name] = $collector;
    }

    /**
     * Fetch collector from registry.
     *
     * @param string $name
     *
     * @return MetricCollectorInterface
     *
     * @throws \OutOfBoundsException
     */
    public function getCollector(string  $name): MetricCollectorInterface
    {
        if (!array_key_exists($name, $this->collectors)) {
            throw new \OutOfBoundsException('Unknown collector in registry: ' . $name);
        }

        return $this->collectors[$name];
    }
}
