<?php

namespace Lamoda\MetricStorage;

use Lamoda\MetricStorage\Exception\MetricStorageException;

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
    public function getAdjustableMetric(string $key): AdjustableMetricInterface
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->hasAdjustableMetric($key)) {
                return $delegate->getAdjustableMetric($key);
            }
        }

        throw MetricStorageException::becauseUnknownKeyInStorage($key);
    }

    /** {@inheritdoc} */
    public function hasAdjustableMetric(string $key): bool
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->hasAdjustableMetric($key)) {
                return true;
            }
        }

        return false;
    }
}
