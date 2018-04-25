<?php

namespace Lamoda\Metric\Storage;

/**
 * @internal
 */
final class StorageRegistry
{
    /** @var array MetricStorageInterface[] */
    private $storages = [];

    public function register(string $name, MetricStorageInterface $storage)
    {
        $this->storages[$name] = $storage;
    }

    /**
     * @param string $name
     *
     * @return MetricStorageInterface
     *
     * @throws \OutOfBoundsException
     */
    public function getStorage(string $name): MetricStorageInterface
    {
        if (!array_key_exists($name, $this->storages)) {
            throw new \OutOfBoundsException('Unknown storage in registry: ' . $name);
        }

        return $this->storages[$name];
    }
}
