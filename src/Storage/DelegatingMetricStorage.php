<?php

namespace Lamoda\Metric\Storage;

use Lamoda\Metric\Storage\Exception\MetricStorageException;

final class DelegatingMetricStorage implements AdjustableMetricStorageInterface
{
    /** @var AdjustableMetricStorageInterface[] */
    private $delegates;

    /**
     * DelegatingAdjusterResolver constructor.
     *
     * @param AdjustableMetricStorageInterface[] $delegates
     */
    public function __construct(array $delegates = [])
    {
        $this->delegates = $delegates;
    }

    public function delegate(AdjustableMetricStorageInterface $resolver)
    {
        $this->delegates[] = $resolver;
    }

    /** {@inheritdoc} */
    public function getAdjustableMetric(string $name, array $tags = []): MutableMetricInterface
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->hasAdjustableMetric($name)) {
                return $delegate->getAdjustableMetric($name);
            }
        }

        throw MetricStorageException::becauseUnknownKeyInStorage($name);
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key, array $tags = []): bool
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->hasAdjustableMetric($key)) {
                return true;
            }
        }

        return false;
    }
}
