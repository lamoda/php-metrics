<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Lamoda\Metric\Adapters\Doctrine\DoctrineMetricSource;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class Source
{
    const TAG = 'lamoda_metrics.source';
    const ALIAS_ATTRIBUTE = 'alias';

    const ID_PREFIX = 'lamoda_metric.metric_source.';

    const METRIC_SOURCE_TYPES
        = [
            self::METRIC_SOURCE_DOCTRINE,
            self::METRIC_SOURCE_SERVICE,
            self::METRIC_SOURCE_COMPOSITE,
        ];

    const METRIC_SOURCE_COMPOSITE = 'composite';
    const METRIC_SOURCE_SERVICE = 'service';
    const METRIC_SOURCE_DOCTRINE = 'doctrine';

    public static function register(ContainerBuilder $container, string $name, array $config)
    {
        switch ($config['type']) {
            case static::METRIC_SOURCE_COMPOSITE:
                static::createCompositeMetricDefinition($container, $name, $config);
                break;
            case static::METRIC_SOURCE_SERVICE:
                static::createServiceMetricDefinition($container, $name, $config);
                break;
            case static::METRIC_SOURCE_DOCTRINE:
                static::createDoctrineMetricDefinition($container, $name, $config);
                break;
            default:
                throw new \InvalidArgumentException('Invalid metric source type: ' . $config['type']);
        }
    }

    public static function createId(string $name): string
    {
        return self::ID_PREFIX . $name;
    }

    public static function createReference(string $name): Reference
    {
        return new Reference(self::createId($name));
    }

    private static function createServiceMetricDefinition(ContainerBuilder $container, string $name, array $config)
    {
        if (!array_key_exists('id', $config) || !is_string($config['id'])) {
            throw new \InvalidArgumentException('`id` key should be configured for metric source');
        }

        $container->setAlias(self::createId($name), $config['id']);
    }

    private static function createCompositeMetricDefinition(ContainerBuilder $container, string $name, array $config)
    {
        if (!array_key_exists('metrics', $config) || !is_array($config['metrics'])) {
            throw new \InvalidArgumentException('`metrics` key should be configured for composite source');
        }

        $metrics = [];

        foreach ($config['metrics'] as $ref) {
            $metrics[] = new Reference($ref);
        }

        $definition = new Definition(IterableMetricSource::class, [new Definition(\ArrayIterator::class, [$metrics])]);
        if (class_exists(IteratorArgument::class)) {
            $definition = new Definition(IterableMetricSource::class, [new IteratorArgument($metrics)]);
        }

        $container->setDefinition(self::createId($name), $definition);
    }

    /**
     * @param array $config
     *
     * @return Definition
     *
     * @throws \InvalidArgumentException
     */
    private static function createDoctrineMetricDefinition(ContainerBuilder $container, string $name, array $config)
    {
        if (!array_key_exists('entity', $config) || !is_string($config['entity'])) {
            throw new \InvalidArgumentException('`entity` key should be configured for doctrine source');
        }

        $container->setDefinition(
            self::createId($name),
            new Definition(
                DoctrineMetricSource::class,
                [
                    new Reference('doctrine'),
                    $config['entity'],
                ]
            )
        );
    }
}
