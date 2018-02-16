<?php

namespace Lamoda\MetricBundle\DependencyInjection;

use Lamoda\MetricResponder\GroupSource\DoctrineMetricGroupSource;
use Lamoda\MetricResponder\GroupSource\MergingMetricGroupSource;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class MetricGroupSourceDefinitionFactory
{
    const METRIC_GROUP_SOURCE_TYPES
        = [
            self::METRIC_GROUP_SOURCE_DOCTRINE,
            self::METRIC_GROUP_SOURCE_SERVICE,
            self::METRIC_GROUP_SOURCE_MERGING,
        ];

    const METRIC_GROUP_SOURCE_MERGING = 'merging';
    const METRIC_GROUP_SOURCE_SERVICE = 'service';
    const METRIC_GROUP_SOURCE_DOCTRINE = 'doctrine';

    /**
     * @param array $config
     *
     * @return Definition|Reference
     *
     * @throws \InvalidArgumentException
     */
    public static function createDefinition(array $config)
    {
        switch ($config['type']) {
            case static::METRIC_GROUP_SOURCE_MERGING:
                return static::createMergingMetricGroupSource($config);
            case static::METRIC_GROUP_SOURCE_SERVICE:
                return static::createServiceMetricGroupDefinition($config);
            case static::METRIC_GROUP_SOURCE_DOCTRINE:
                return static::createDoctrineMetricGroupDefinition($config);
            default:
                throw new \InvalidArgumentException('Invalid metric group source type: ' . $config['type']);
        }
    }

    /**
     * @param array $config
     *
     * @return Reference
     *
     * @throws \InvalidArgumentException
     */
    private static function createServiceMetricGroupDefinition(array $config)
    {
        if (!array_key_exists('id', $config) || !is_string($config['id'])) {
            throw new \InvalidArgumentException('`id` key should be configured for metric group source');
        }

        return new Reference($config['id']);
    }

    /**
     * @param array $config
     *
     * @return Definition
     *
     * @throws \InvalidArgumentException
     */
    private static function createMergingMetricGroupSource(array $config)
    {
        if (!array_key_exists('groups', $config) || !is_array($config['groups'])) {
            throw new \InvalidArgumentException('`groups` key should be configured for merging metric group source');
        }

        $groups = [];

        foreach ($config['groups'] as $ref) {
            $groups[] = new Reference($ref);
        }

        return new Definition(MergingMetricGroupSource::class, [new IteratorArgument($groups)]);
    }

    /**
     * @param array $config
     *
     * @return Definition
     *
     * @throws \InvalidArgumentException
     */
    private static function createDoctrineMetricGroupDefinition(array $config)
    {
        if (!array_key_exists('entity', $config) || !is_string($config['entity'])) {
            throw new \InvalidArgumentException('`entity` key should be configured for doctrine source');
        }

        return new Definition(
            DoctrineMetricGroupSource::class,
            [
                new Reference('doctrine'),
                $config['entity'],
            ]
        );
    }
}
