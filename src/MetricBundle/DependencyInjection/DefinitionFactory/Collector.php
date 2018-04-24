<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Lamoda\Metric\Collector\MergingCollector;
use Lamoda\Metric\Collector\SingeSourceCollector;
use Lamoda\Metric\Common\Source\IterableMetricSource;
use Lamoda\Metric\Common\Source\MergingMetricSource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class Collector
{
    const TAG = 'lamoda_metrics.collector';
    const ALIAS_ATTRIBUTE = 'alias';

    const ID_PREFIX = 'lamoda_metrics.collector.';

    const COLLECTOR_TYPE_SERVICE = 'service';
    const COLLECTOR_TYPE_PRECONFIGURED = 'sources';
    const COLLECTOR_TYPE_MERGING = 'merge';

    const TYPES = [
        self::COLLECTOR_TYPE_SERVICE,
        self::COLLECTOR_TYPE_PRECONFIGURED,
        self::COLLECTOR_TYPE_MERGING,
    ];
    const REGISTRY_ID = 'lamoda_metrics.collector_registry';

    public static function createId(string $name): string
    {
        return self::ID_PREFIX . $name;
    }

    public static function register(ContainerBuilder $container, string $name, array $config)
    {
        switch ($config['type']) {
            case self::COLLECTOR_TYPE_PRECONFIGURED:
                self::registerPreconfigured($container, $name, $config);
                break;
            case self::COLLECTOR_TYPE_MERGING:
                self::registerMerging($container, $name, $config);
                break;
            case self::COLLECTOR_TYPE_SERVICE:
                $container->getDefinition(self::REGISTRY_ID)->addMethodCall(
                    'register',
                    [$name, self::createReference($name)]
                );
                $container->setAlias(self::createId($name), $config['id']);
                break;
        }

        $registry = $container->getDefinition(Collector::REGISTRY_ID);
        $registry->addMethodCall('register', [$name, self::createReference($name)]);
    }

    public static function createReference(string $name): Reference
    {
        return new Reference(self::createId($name));
    }

    private static function registerMerging(ContainerBuilder $container, string $name, array $config)
    {
        $definition = $container->register(self::createId($name), MergingCollector::class);
        $collectorNames = $config['collectors'];
        $refs = array_map([self::class, 'createReference'], $collectorNames);
        $definition->setArguments([$refs, $config['tags']]);
    }

    private static function registerPreconfigured(ContainerBuilder $container, string $name, array $config)
    {
        $definition = $container->register(self::createId($name), SingeSourceCollector::class);
        $sources = [];
        foreach ($config['sources'] as $sourceAlias) {
            $sources[] = Source::createReference($sourceAlias);
        }

        if (!empty($config['metrics'])) {
            $metricServices = [];
            foreach ($config['metrics'] as $metricService) {
                $metricServices[] = new Reference($metricService);
            }
            $sources[] = new Definition(IterableMetricSource::class, [$metricServices]);
        }

        $definition->setArguments(
            [new Definition(MergingMetricSource::class, $sources), $config['tags']]
        );
    }
}
