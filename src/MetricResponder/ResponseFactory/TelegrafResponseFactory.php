<?php

namespace Lamoda\MetricResponder\ResponseFactory;

use GuzzleHttp\Psr7\Response;
use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\MetricInterface;
use Lamoda\MetricResponder\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class TelegrafResponseFactory implements ResponseFactoryInterface
{
    const CONTENT_TYPE = 'application/json';

    /** {@inheritdoc} */
    public function create(MetricGroupSourceInterface $source, string $prefix = ''): ResponseInterface
    {
        $normalizedGroups = [];
        foreach ($source as $group) {
            $normalizedGroups[] = $this->formatGroup($group, $prefix);
        }

        return new Response(
            200,
            ['Content-Type' => self::CONTENT_TYPE],
            json_encode(array_values(array_filter($normalizedGroups)))
        );
    }

    /**
     * Convert metric group to serializable structure.
     *
     * @param MetricGroupInterface $group
     * @param string $prefix
     *
     * @return array
     */
    private function formatGroup(MetricGroupInterface $group, string $prefix): array
    {
        $data = $this->doFormat($group, $prefix);

        if (empty($data)) {
            return [];
        }

        foreach ($group->getTags() as $tag => $value) {
            $data[$tag] = (string) $value;
        }

        return $data;
    }

    /**
     * @param MetricGroupInterface|MetricInterface[] $container
     * @param string $prefix
     *
     * @return array
     */
    private function doFormat(MetricGroupInterface $container, string $prefix): array
    {
        $result = [];
        foreach ($container as $metric) {
            $value = $metric->resolve();
            if (is_numeric($value)) {
                $value = (float) $value;
            }

            $result[$prefix . $metric->getName()] = $value;
        }

        return $result;
    }
}
