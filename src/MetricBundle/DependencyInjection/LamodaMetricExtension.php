<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection;

use Lamoda\Metric\MetricBundle\Controller\HttpFoundationResponder;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Receiver;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Responder;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\ResponseFactory;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Source;
use Lamoda\Metric\Responder\PsrResponder;
use Lamoda\Metric\Storage\Decorators\ResolvableMetricSource;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class LamodaMetricExtension extends ConfigurableExtension
{
    public function getAlias(): string
    {
        return 'lamoda_metrics';
    }

    /** {@inheritdoc} */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('response_factories.yml');
        $loader->load('services.yml');

        $this->processFactories($container, $mergedConfig['response_factories'] ?? []);
        $this->processSources($container, $mergedConfig['sources'] ?? []);
        $this->processCollectors($container, $mergedConfig['collectors'] ?? []);
        $this->processResponders($container, $mergedConfig['responders'] ?? []);
        $this->processReceivers($container, $mergedConfig['receivers'] ?? []);
    }

    private function processFactories(ContainerBuilder $container, array $config)
    {
        foreach ($config as $name => $factoryConfig) {
            ResponseFactory::register($container, $name, $factoryConfig);
        }
    }

    private function processCollectors(ContainerBuilder $container, array $config)
    {
        foreach ($config as $name => $collectorConfig) {
            if (!$collectorConfig['enabled']) {
                continue;
            }

            Collector::register($container, $name, $collectorConfig);
        }
    }

    private function processReceivers(ContainerBuilder $container, array $config)
    {
        foreach ($config as $name => $receiverConfig) {
            if (!$receiverConfig['enabled']) {
                continue;
            }

            Receiver::register($container, $name, $receiverConfig);
        }
    }

    private function processSources(ContainerBuilder $container, array $sources)
    {
        $resolverDelegate = $container->getDefinition('lamoda_metrics.metric_storage');

        foreach ($sources as $name => $sourceConfig) {
            Source::register($container, $name, $sourceConfig);

            $reference = Source::createReference($name);

            if ($sourceConfig['storage']) {
                $resolver = $reference;
                if ($sourceConfig['type'] !== 'doctrine') {
                    $resolver = new Definition(ResolvableMetricSource::class, [$reference]);
                }

                $resolverDelegate->addMethodCall('delegate', [$resolver]);
            }
        }
    }

    private function processResponders(ContainerBuilder $container, array $config)
    {
        $routerLoader = $container->getDefinition('lamoda_metrics.route_loader');

        foreach ($config as $name => $responderConfig) {
            if (!$responderConfig['enabled']) {
                continue;
            }

            $controllerId = Responder::createId($name);

            $psrController = new Definition(PsrResponder::class);
            $psrController->setPublic(false);
            $psrController->setArguments(
                [
                    Collector::createReference($responderConfig['collector']),
                    ResponseFactory::createReference($responderConfig['response_factory'] ?? $name),
                    $responderConfig['format_options'] ?? [],
                ]
            );

            $controller = $container->register($controllerId, HttpFoundationResponder::class);
            $controller->setPublic(true);
            $controller->setArguments([$psrController]);

            $path = $responderConfig['path'] ?? '/' . $name;
            $routerLoader->addMethodCall('registerController', [$name, $path, $controllerId, 'createResponse']);
        }
    }
}
