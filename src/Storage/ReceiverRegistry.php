<?php

namespace Lamoda\Metric\Storage;

/**
 * @internal
 */
final class ReceiverRegistry
{
    /** @var array MetricReceiverInterface[] */
    private $receivers = [];

    public function register(string $name, MetricReceiverInterface $source)
    {
        $this->receivers[$name] = $source;
    }

    /**
     * @param string $name
     *
     * @return MetricReceiverInterface
     *
     * @throws \OutOfBoundsException
     */
    public function getReceiver(string $name): MetricReceiverInterface
    {
        if (!array_key_exists($name, $this->receivers)) {
            throw new \OutOfBoundsException('Unknown receiver in registry: ' . $name);
        }

        return $this->receivers[$name];
    }
}
