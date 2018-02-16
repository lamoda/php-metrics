<?php

namespace Lamoda\MetricResponder\ResponseFactory;

use GuzzleHttp\Psr7\Response;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Converts metrics for Prometheus response.
 *
 * @see PrometheusResponseFactory
 */
final class PrometheusResponseFactory implements ResponseFactoryInterface
{
    const FORMAT_LINE = '%s{%s} %s';
    const FORMAT_LABEL = '%s="%s"';
    const GLUE_LABELS = ',';
    const GLUE_LINES = PHP_EOL;

    /** Not `A-Z`, `0-9` or `_`. */
    const PATTERN_FILTER_LABEL_NAME = '/\W/';

    const CONTENT_TYPE = 'text/plain; version=0.0.4';

    /**
     * {@inheritdoc}
     */
    public function create(MetricGroupSourceInterface $source, string $prefix = ''): ResponseInterface
    {
        $data = [];
        foreach ($source as $group) {
            $this->addGroupData($group, $prefix, $data);
        }

        return new Response(
            200,
            ['Content-Type' => self::CONTENT_TYPE],
            $this->getContent($data)
        );
    }

    /**
     * @param MetricGroupInterface | MetricInterface[] $group
     * @param string $prefix
     * @param array $data
     */
    private function addGroupData(MetricGroupInterface $group, string $prefix, array &$data)
    {
        $groupTags = array_map('\strval', $group->getTags()) ?: [];

        foreach ($group as $metric) {
            $value = $this->getMetricValue($metric);
            $data[] = [
                'name' => $prefix . $metric->getName(),
                'value' => $value,
                'tags' => $groupTags,
            ];
        }
    }

    /**
     * @param $metric
     *
     * @return mixed
     */
    private function getMetricValue(MetricInterface $metric)
    {
        $value = $metric->resolve();

        return is_numeric($value) ? (float) $value : $value;
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
     * @param string $name
     * @param array $tags
     * @param null $value
     *
     * @return null|string
     */
    private function getLine(string $name, array $tags, $value = null)
    {
        if ($value === null) {
            return null;
        }

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
        $tagsString = [];
        foreach ($labels as $name => $value) {
            $name = $this->formatLabelName($name);
            $value = $this->formatLabelValue($value);
            $tagsString[] = sprintf(self::FORMAT_LABEL, $name, $value);
        }

        return implode(self::GLUE_LABELS, $tagsString);
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
