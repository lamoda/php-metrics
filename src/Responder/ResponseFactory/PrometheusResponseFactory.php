<?php

namespace Lamoda\Metric\Responder\ResponseFactory;

use GuzzleHttp\Psr7\Response;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Responder\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Creates Prometheus formatted metrics response.
 */
final class PrometheusResponseFactory implements ResponseFactoryInterface
{
    private const FORMAT_LINE = '%s%s %s';
    private const LABELS_ENCLOSURE = '{%s}';
    private const FORMAT_LABEL = '%s="%s"';
    private const GLUE_LABELS = ',';
    private const GLUE_LINES = PHP_EOL;

    /** Not `A-Z`, `0-9` or `_`. */
    private const PATTERN_FILTER_LABEL_NAME = '/\W/';

    private const CONTENT_TYPE = 'text/plain; version=0.0.4';

    /**
     * {@inheritdoc}
     */
    public function create(MetricSourceInterface $source, array $options = []): ResponseInterface
    {
        $data = [];
        $histogramMetricsData = [];

        foreach ($source->getMetrics() as $metric) {
            $tags = $metric->getTags();

            if (isset($tags['_meta']) && ($tags['_meta']['type'] ?? '') === 'histogram') {
                $histogramMetricsData = $this->prepareHistogramMetric($metric, $histogramMetricsData);
                continue;
            }

            $data[] = [
                'name' => ($options['prefix'] ?? '') . $metric->getName(),
                'value' => $metric->resolve(),
                'tags' => $tags,
            ];
        }
        
        $histogramData = $this->calculateHistogramMetric($histogramMetricsData);

        return new Response(
            200,
            ['Content-Type' => self::CONTENT_TYPE],
            $this->getContent(array_merge($data, $histogramData))
        );
    }

    private function buildHistogramMetricHash(MetricInterface $metric): string
    {
        return md5($metric->getName() . implode('', $this->clearTags($metric->getTags())));
    }

    /**
     * @param array<string, string> $tags
     * @return array<string, string>
     */
    private function clearTags(array $tags): array
    {
        if (isset($tags['_meta'])) {
            unset($tags['_meta']);
        }

        if (isset($tags['le'])) {
            unset($tags['le']);
        }

        return $tags;
    }

    /**
     * @param array<string, array<string, mixed>> $preparedHistogramMetricsData
     * @return array<string, array<string, mixed>>
     */
    private function prepareHistogramMetric(MetricInterface $metric, array $preparedHistogramMetricsData): array
    {
        $tags = $metric->getTags();
        $metaTags = $tags['_meta'] ?? null;
        $le = $tags['le'] ?? null;
        $tags = $this->clearTags($tags);
        $keyMetric = $this->buildHistogramMetricHash($metric);

        if (!isset($preparedHistogramMetricsData[$keyMetric])) {
            $preparedHistogramMetricsData[$keyMetric] = [
                'name' => $metric->getName(),
                'buckets' => $metaTags['buckets'],
                'tags' => $tags,
                'data' => [],
                'sum' => 0,
            ];
        }

        if (isset($metaTags['is_sum'])) {
            $preparedHistogramMetricsData[$keyMetric]['sum'] = $metric->resolve();
        }

        if($le !== null) {
            $preparedHistogramMetricsData[$keyMetric]['data'][(string) $le] = $metric->resolve();
        }

        return $preparedHistogramMetricsData;
    }

    /**
     * @return array<string, array<int, mixed>> $histogramMetricsData
     * @return array<int, array<string, string>>
     */
    private function calculateHistogramMetric(array $histogramMetricsData): array
    {
        $data = [];
        foreach ($histogramMetricsData as $histogramMetricData) {
            $total = 0;

            foreach ($histogramMetricData['buckets'] as $bucket) {
                $value = $histogramMetricData['data'][(string)$bucket] ?? null;
                $total += $value;

                $data[] = [
                    'name' => ($options['prefix'] ?? '') . $histogramMetricData['name'] . '_bucket',
                    'value' => $total,
                    'tags' => array_merge($histogramMetricData['tags'], ['le' => (string) $bucket])
                ];
            }

            if (count($histogramMetricData['data']) > 0) {
                $data[] = [
                    'name' => ($options['prefix'] ?? '') . $histogramMetricData['name'] . '_sum',
                    'value' => $histogramMetricData['sum'],
                    'tags' => $histogramMetricData['tags'],
                ];

                $data[] = [
                    'name' => ($options['prefix'] ?? '') . $histogramMetricData['name'] . '_count',
                    'value' => $total,
                    'tags' => $histogramMetricData['tags'],
                ];
            }
        }

        return $data;
    }

    /**
     * Get response content.
     *
     * @param array[] $data
     *
     * @return string
     */
    private function getContent(array $data): string
    {
        $lines = [];
        foreach ($data as $line) {
            $lines[] = $this->getLine($line['name'], $line['tags'], $line['value']);
        }
        $lines = array_filter($lines);

        return implode(self::GLUE_LINES, $lines) . PHP_EOL;
    }

    /**
     * Get single line of Prometheus output.
     *
     * @param string   $name
     * @param string[] $tags
     * @param float    $value
     *
     * @return string
     */
    private function getLine(string $name, array $tags, float $value): string
    {
        return sprintf(self::FORMAT_LINE, $name, $this->formatLabels($tags), $value);
    }

    /**
     * Get tags string.
     *
     * @param array $labels
     *
     * @return string
     */
    private function formatLabels(array $labels): string
    {
        if ($labels === []) {
            return '';
        }

        $tagsString = [];
        foreach ($labels as $name => $value) {
            $name = $this->formatLabelName($name);
            $value = $this->formatLabelValue($value);
            $tagsString[] = sprintf(self::FORMAT_LABEL, $name, $value);
        }

        return sprintf(self::LABELS_ENCLOSURE, implode(self::GLUE_LABELS, $tagsString));
    }

    /**
     * Add slashes to values.
     *
     * @param $value
     *
     * @return mixed
     */
    private function formatLabelValue(string $value): string
    {
        return addcslashes($value, "\n\"\\");
    }

    /**
     * Remove unsupported symbols from.
     *
     * @param string $name
     *
     * @return string
     */
    private function formatLabelName(string $name): string
    {
        // Only letters, digits and slashes.
        return preg_replace(self::PATTERN_FILTER_LABEL_NAME, '', $name);
    }
}
