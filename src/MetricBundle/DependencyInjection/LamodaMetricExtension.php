<?php

namespace Lamoda\MetricBundle\DependencyInjection;

use Lamoda\MetricBundle\Controller\ResponderController;
use Lamoda\MetricInfra\Decorators\ResolvableMetricGroup;
use Lamoda\MetricInfra\Decorators\ResolvableMetricGroupSource;
use Lamoda\MetricInfra\Decorators\ResolvableMetricSource;
use Lamoda\MetricResponder\GroupSource\CompositeMetricGroupSource;
use Lamoda\MetricResponder\MetricGroup\CombinedMetricGroup;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class LamodaMetricExtension extends ConfigurableExtension
{
    /** @var Definition */
    private $resolverDelegate;

    public function getAlias()
    {
        return 'lamoda_metrics';
    }

    /** {@inheritdoc} */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->resolverDelegate = $container->getDefinition('lamoda_metrics.metric_storage');

        $metricSourceReferences = $this->processMetricSources($container, $mergedConfig);
        $groupSourceReferences = $this->processGroupSources($container, $mergedConfig);
        $groupsReferences = $this->processCustomGroups($container, $mergedConfig, $metricSourceReferences);

        $this->registerResponders($mergedConfig, $container, $groupsReferences, $groupSourceReferences);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $mergedConfig
     *
     * @return Reference[]
     */
    private function processMetricSources(ContainerBuilder $container, array $mergedConfig): array
    {
        $metricSources = $mergedConfig['metrics']['sources'];
        $metricSourceReferences = [];
        $prefix = 'lamoda_metric.metric_source.';
        foreach ($metricSources as $name => $sourceConfig) {
            $id = $prefix . $name;
            $definition = MetricSourceDefinitionFactory::createDefinition($sourceConfig);

            if ($definition instanceof Definition) {
                $definition->setPublic(false);
                $container->setDefinition($id, $definition);
            } elseif ($definition instanceof Reference) {
                $container->setAlias($id, (string) $definition);
                $id = (string) $definition;
            } else {
                throw new \LogicException('Factory should return either instance of Definition or Reference');
            }

            if ($sourceConfig['storage']) {
                if ($sourceConfig['type'] !== 'doctrine') {
                    $resolver = new Definition(ResolvableMetricSource::class, [new Reference($id)]);
                } else {
                    $resolver = new Reference($id);
                }

                $this->resolverDelegate->addMethodCall('delegate', [$resolver]);
            }

            $metricSourceReferences[$name] = new Reference($id);
        }

        return $metricSourceReferences;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $mergedConfig
     *
     * @return Reference[]
     */
    private function processGroupSources(ContainerBuilder $container, array $mergedConfig): array
    {
        $groupSourceReferences = [];
        $groupSources = $mergedConfig['groups']['sources'];
        $prefix = 'lamoda_metric.group_source.';
        foreach ($groupSources as $name => $sourceConfig) {
            $id = $prefix . $name;
            $definition = MetricGroupSourceDefinitionFactory::createDefinition($sourceConfig);
            if ($definition instanceof Definition) {
                $definition->setPublic(false);
                $container->setDefinition($id, $definition);
            } elseif ($definition instanceof Reference) {
                $container->setAlias($id, (string) $definition);
                $id = (string) $definition;
            } else {
                throw new \LogicException('Factory should return either instance of Definition or Reference');
            }

            if ($sourceConfig['storage']) {
                if ($sourceConfig['type'] !== 'doctrine') {
                    $resolver = new Definition(ResolvableMetricGroupSource::class, [new Reference($id)]);
                } else {
                    $resolver = new Reference($id);
                }

                $this->resolverDelegate->addMethodCall('delegate', [$resolver]);
            }

            $groupSourceReferences[$name] = new Reference($id);
        }

        return $groupSourceReferences;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $mergedConfig
     * @param Reference[] $metricSourceReferences
     *
     * @return Reference[]
     */
    private function processCustomGroups(
        ContainerBuilder $container,
        array $mergedConfig,
        array $metricSourceReferences
    ): array {
        $groupsReferences = [];
        $customGroups = $mergedConfig['groups']['custom'];
        $prefix = 'lamoda_metric.custom_group.';
        foreach ($customGroups as $name => $groupConfig) {
            $id = $prefix . $name;
            $definition = $container->register($id, CombinedMetricGroup::class);
            $definition->setArguments([$name, $groupConfig['tags']]);
            $definition->setPublic(false);

            foreach ($groupConfig['metric_sources'] as $metricSourceName) {
                $definition->addMethodCall('addSource', [$metricSourceReferences[$metricSourceName]]);
            }

            foreach ($groupConfig['metric_services'] as $metricId) {
                $definition->addMethodCall('addMetric', [new Reference($metricId)]);
            }

            if ($groupConfig['storage']) {
                $resolver = new Definition(ResolvableMetricGroup::class, [new Reference($id)]);

                $this->resolverDelegate->addMethodCall('delegate', [$resolver]);
            }

            $groupsReferences[$name] = new Reference($id);
        }

        return $groupsReferences;
    }

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @param Reference[] $groupsReferences
     * @param Reference[] $groupSourceReferences
     */
    private function registerResponders(
        array $mergedConfig,
        ContainerBuilder $container,
        array $groupsReferences,
        array $groupSourceReferences
    ) {
        $routerLoader = $container->getDefinition('lamoda_metrics.route_loader');

        foreach ($mergedConfig['responders'] as $name => $responderConfig) {
            $controllerId = 'lamoda_metrics.controller.' . $name;
            $controller = $container->register($controllerId, ResponderController::class);
            $source = new Definition(CompositeMetricGroupSource::class);
            foreach ($responderConfig['groups'] as $groupName) {
                $source->addMethodCall('addGroup', [$groupsReferences[$groupName]]);
            }
            foreach ($responderConfig['sources'] as $sourceName) {
                $source->addMethodCall('addSource', [$groupSourceReferences[$sourceName]]);
            }

            $factoryId = $responderConfig['response_factory'] ?? 'lamoda_metrics.response_factory.' . $name;
            $factory = new Reference($factoryId);

            $controller->setPublic(true);
            $controller->setArguments([$source, $factory, $responderConfig['prefix']]);

            $path = $responderConfig['path'] ?? '/' . $name;

            $routerLoader->addMethodCall('registerController', [$path, $controllerId]);
        }
    }
}
