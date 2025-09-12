<?php

namespace Lamoda\Metric\Adapters\Redis;

class HistogramMetricDto
{
    /** @var string */
    public $name;
    /** @var float */
    public $value;
    /** @var array<string, string> */
    public $tags;
    /** @var float[] */
    public $buckets;
    /** @var string */
    public $le;

    /**
     * @param array<string, string> $tags
     * @param float[]|int[] $buckets
     */
    public function __construct(string $name, float $value, array $buckets, array $tags)
    {
        $this->name = $name;
        $this->value = $value;
        $this->tags = $tags;
        sort($buckets);
        $this->buckets = $buckets;
        $this->le = $this->calculateLe($buckets, $value);
    }

    /**
     * @param float[] $buckets
     * @param float $value
     * @return string
     */
    private function calculateLe(array $buckets, float $value): string
    {
        $bucketToIncrease = '+Inf';
        foreach ($buckets as $bucket) {
            if ($value <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }

        return (string) $bucketToIncrease;
    }
}
