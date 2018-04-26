<?php

namespace Lamoda\Metric\Responder\ResponseFactory;

use GuzzleHttp\Psr7\Response;
use Lamoda\Metric\Common\MetricSourceInterface;
use Lamoda\Metric\Responder\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Converts metrics for Prometheus response.
 *
 * @see PrometheusResponseFactory
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
        foreach ($source->getMetrics() as $metric) {
            $data[] = [
                'name' => ($options['prefix'] ?? '') . $metric->getName(),
                'value' => $metric->resolve(),
                'tags' => $metric->getTags(),
            ];
        }

        return new Response(
            200,
            ['Content-Type' => self::CONTENT_TYPE],
            $this->getContent($data)
        );
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
     * @param null     $value
     *
     * @return null|string
     */
    private function getLine(string $name, array $tags, $value = null): ?string
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
