<?php

declare(strict_types=1);

namespace Lamoda\Metric\Adapters\Redis;

/** @internal */
final class RedisConnection implements RedisConnectionInterface
{
    private const DEFAULT_METRICS_KEY = '__php_metrics';

    /** @var \Predis\Client|\Redis */
    private $client;
    /** @var string */
    private $metricsKey;

    /**
     * @param \Predis\Client|\Redis $client
     * @param string|null $metricsKey
     */
    public function __construct($client, ?string $metricsKey = self::DEFAULT_METRICS_KEY)
    {
        $this->client = $client;
        $this->metricsKey = $metricsKey;
    }

    /** {@inheritdoc} */
    public function getAllMetrics(): array
    {
        $rawMetricsData = $this->client->hgetall($this->metricsKey);
        $metrics = [];
        foreach ($rawMetricsData as $rawMetricData => $value) {
            $metricData = json_decode($rawMetricData, true);
            $metrics[] = new MetricDto(
                $metricData['name'],
                (float) $value,
                $this->convertTagsFromStorage($metricData['tags'])
            );
        }

        return $metrics;
    }

    /** {@inheritdoc} */
    public function adjustMetric(string $key, float $delta, array $tags): float
    {
        return (float) $this->client->hincrbyfloat($this->metricsKey, $this->buildField($key, $tags), $delta);
    }

    /** {@inheritdoc} */
    public function setMetrics(array $metricsData): void
    {
        $fields = [];
        foreach ($metricsData as $metricDto) {
            $field = $this->buildField($metricDto->name, $metricDto->tags);
            $fields[$field] = $metricDto->value;
        }
        $this->client->hmset($this->metricsKey, $fields);
    }

    /** {@inheritdoc} */
    public function getMetricValue(string $key, array $tags): ?float
    {
        $value = $this->client->hget($this->metricsKey, $this->buildField($key, $tags));
        if ($value === false) {
            return null;
        }

        return (float) $value;
    }

    private function buildField(string $name, array $tags)
    {
        return json_encode([
            'name' => $name,
            'tags' => $this->convertTagsForStorage($tags),
        ]);
    }

    private function convertTagsForStorage(array $tags): string
    {
        return json_encode($this->normalizeTags($tags));
    }

    private function convertTagsFromStorage(string $tags): array
    {
        return json_decode($tags, true);
    }

    private function normalizeTags(array $tags): array
    {
        ksort($tags);

        return $tags;
    }

}
