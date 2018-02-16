<?php

namespace Lamoda\MetricBundle\DependencyInjection;

use Lamoda\MetricInfra\MetricSource\DoctrineMetricSource;
use Lamoda\MetricResponder\MetricSource\CompositeMetricSource;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class MetricSourceDefinitionFactory
{
    const METRIC_SOURCE_TYPES
        = [
            self::METRIC_SOURCE_DOCTRINE,
            self::METRIC_SOURCE_SERVICE,
            self::METRIC_SOURCE_COMPOSITE,
        ];

    const METRIC_SOURCE_COMPOSITE = 'composite';
    const METRIC_SOURCE_SERVICE = 'service';
    const METRIC_SOURCE_DOCTRINE = 'doctrine';

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
            case static::METRIC_SOURCE_COMPOSITE:
                return static::createCompositeMetricDefinition($config);
            case static::METRIC_SOURCE_SERVICE:
                return static::createServiceMetricDefinition($config);
            case static::METRIC_SOURCE_DOCTRINE:
                return static::createDoctrineMetricDefinition($config);
            default:
                throw new \InvalidArgumentException('Invalid metric source type: ' . $config['type']);
        }
    }

    /**
     * @param array $config
     *
     * @return Reference
     *
     * @throws \InvalidArgumentException
     */
    private static function createServiceMetricDefinition(array $config)
    {
        if (!array_key_exists('id', $config) || !is_string($config['id'])) {
            throw new \InvalidArgumentException('`id` key should be configured for metric source');
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
    private static function createCompositeMetricDefinition(array $config)
    {
        if (!array_key_exists('metrics', $config) || !is_array($config['metrics'])) {
            throw new \InvalidArgumentException('`metrics` key should be configured for composite source');
        }

        $metrics = [];

        foreach ($config['metrics'] as $ref) {
            $metrics[] = new Reference($ref);
        }

        if (class_exists(IteratorArgument::class)) {
            return new Definition(CompositeMetricSource::class, [new IteratorArgument($metrics)]);
        }

        return new Definition(CompositeMetricSource::class, [new Definition(\ArrayIterator::class, [$metrics])]);
    }

    /**
     * @param array $config
     *
     * @return Definition
     *
     * @throws \InvalidArgumentException
     */
    private static function createDoctrineMetricDefinition(array $config)
    {
        if (!array_key_exists('entity', $config) || !is_string($config['entity'])) {
            throw new \InvalidArgumentException('`entity` key should be configured for doctrine source');
        }

        return new Definition(
            DoctrineMetricSource::class,
            [
                new Reference('doctrine'),
                $config['entity'],
            ]
        );
    }
}
