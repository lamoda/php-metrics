<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Lamoda\Metric\Common\Source\IterableMetricSource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class Source
{
    public const TAG = 'lamoda_metrics.source';
    public const ALIAS_ATTRIBUTE = 'alias';

    public const METRIC_SOURCE_TYPES = [
        self::METRIC_SOURCE_SERVICE,
        self::METRIC_SOURCE_COMPOSITE,
        self::METRIC_SOURCE_STORAGE,
    ];

    private const ID_PREFIX = 'lamoda_metric.metric_source.';

    private const METRIC_SOURCE_COMPOSITE = 'composite';
    private const METRIC_SOURCE_SERVICE = 'service';
    private const METRIC_SOURCE_STORAGE = 'storage';

    public static function register(ContainerBuilder $container, string $name, array $config)
    {
        switch ($config['type']) {
            case static::METRIC_SOURCE_COMPOSITE:
                static::createCompositeMetricDefinition($container, $name, $config);
                break;
            case static::METRIC_SOURCE_SERVICE:
                static::createServiceMetricDefinition($container, $name, $config);
                break;
            case static::METRIC_SOURCE_STORAGE:
                static::createServiceMetricDefinition(
                    $container,
                    $name,
                    ['id' => Storage::createId($config['storage'])]
                );
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

        $container->setDefinition(self::createId($name), $definition);
    }
}
