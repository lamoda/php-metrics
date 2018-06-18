<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\ResponseFactory;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Source;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Storage;
use Lamoda\Metric\Storage\MetricStorageInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** {@inheritdoc} */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder();

        $root = $builder->root('lamoda_metrics');
        $root->addDefaultsIfNotSet();

        $sources = $root->children()->arrayNode('sources');
        $sources->useAttributeAsKey('name', false);
        $this->createSources($sources->prototype('array'));

        $responseFactories = $root->children()->arrayNode('response_factories');
        $responseFactories->useAttributeAsKey('name', false);
        $this->createResponseFactory($responseFactories->prototype('array'));

        $responders = $root->children()->arrayNode('responders');
        $responders->useAttributeAsKey('name', false);
        $this->createResponder($responders->prototype('array'));

        $storages = $root->children()->arrayNode('storages');
        $storages->useAttributeAsKey('name', false);
        $this->createStorage($storages->prototype('array'));

        $collectors = $root->children()->arrayNode('collectors');
        $collectors->useAttributeAsKey('name', false);
        $this->createCollector($collectors->prototype('array'));

        return $builder;
    }

    private function createSources(ArrayNodeDefinition $source): void
    {
        $source->info(
            'Sources also can be configured as services via `' . DefinitionFactory\Source::TAG . '` tag with `' . DefinitionFactory\Source::ALIAS_ATTRIBUTE . '` attribute'
        );
        $source->canBeDisabled();
        $source->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(Source::METRIC_SOURCE_TYPES)
            ->info('Type of the source');

        $source->children()
            ->scalarNode('id')
            ->defaultNull()
            ->info('Source service identifier [service]');

        $source->children()
            ->scalarNode('entity')
            ->defaultValue(MetricInterface::class)
            ->info('Entity class [doctrine]');

        $source->children()
            ->arrayNode('metrics')
            ->info('Metric services [composite]')
            ->defaultValue([])
            ->prototype('scalar');

        $source->children()
            ->scalarNode('storage')
            ->info('Storage name [storage]')
            ->defaultNull();
    }

    private function createResponseFactory(ArrayNodeDefinition $responseFactory): void
    {
        $responseFactory->info(
            'Response factories also can be configured as services via `' . DefinitionFactory\ResponseFactory::TAG . '` tag with `' . DefinitionFactory\ResponseFactory::ALIAS_ATTRIBUTE . '` attribute'
        );
        $responseFactory->canBeDisabled();
        $responseFactory->beforeNormalization()->ifString()->then(
            function (string $v) {
                return ['type' => 'service', 'id' => $v];
            }
        );
        $responseFactory->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(ResponseFactory::TYPES)
            ->info('Type of the factory');

        $responseFactory->children()
            ->scalarNode('id')
            ->defaultNull()
            ->info('Response factory service identifier [service]');
    }

    private function createCollector(ArrayNodeDefinition $collector): void
    {
        $collector->info(
            'Collectors also can be configured as services via `' . DefinitionFactory\Collector::TAG . '` tag with `' . DefinitionFactory\Collector::ALIAS_ATTRIBUTE . '` attribute'
        );
        $collector->beforeNormalization()->ifString()->then(
            function (string $v) {
                return ['type' => Collector::COLLECTOR_TYPE_SERVICE, 'id' => $v];
            }
        );
        $collector->canBeDisabled();
        $collector->children()->scalarNode('id')
            ->info('Collector service ID')
            ->defaultNull()
            ->example(MetricCollectorInterface::class);

        $collector->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(Collector::TYPES)
            ->info('Type of the collector');

        $collector->children()->arrayNode('collectors')
            ->prototype('scalar')
            ->info('Nested collectors')
            ->defaultValue([]);

        $collector->children()->arrayNode('sources')
            ->prototype('scalar')
            ->info('Metrics source names for responder controller')
            ->defaultValue([]);

        $collector->children()->arrayNode('metric_services')
            ->prototype('scalar')
            ->info('Append single metrics from services')
            ->defaultValue([]);

        $collector->children()
            ->arrayNode('default_tags')
            ->defaultValue([])
            ->info('Default tag values for metrics from this collector')
            ->prototype('scalar')
            ->cannotBeEmpty();
    }

    private function createStorage(ArrayNodeDefinition $storage): void
    {
        $storage->info(
            'Storages also can be configured as services via `' . DefinitionFactory\Storage::TAG . '` tag with `' . DefinitionFactory\Storage::ALIAS_ATTRIBUTE . '` attribute'
        );
        $storage->beforeNormalization()->ifString()->then(
            function (string $v) {
                return ['type' => 'service', 'id' => $v];
            }
        );
        $storage->canBeDisabled();
        $storage->children()->scalarNode('id')
            ->cannotBeEmpty()
            ->info('Storage service ID [service]')
            ->example(MetricStorageInterface::class);
        $storage->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(Storage::TYPES)
            ->info('Type of the storage');
        $storage->children()
            ->booleanNode('mutator')
            ->defaultFalse()
            ->info('Configure storage as default metric mutator');
    }

    private function createResponder(ArrayNodeDefinition $responder): void
    {
        $responder->canBeDisabled();
        $responder->children()->scalarNode('path')
            ->cannotBeEmpty()
            ->info('Responder route path. Defaults to /$name')
            ->defaultNull()
            ->example('/prometheus');

        $options = $responder->children()->arrayNode('format_options');
        $options->info('Formatter options');
        $options->ignoreExtraKeys(false);
        $options->children()->scalarNode('prefix')
            ->info('Metrics prefix for responder')
            ->defaultValue('')
            ->example('project_name_');
        $options->children()->arrayNode('propagate_tags')
            ->info('Propagate tags to group [telegraf_json]')
            ->prototype('scalar')
            ->defaultValue([])
            ->example('type');
        $options->children()->arrayNode('group_by_tags')
            ->info('Arrange metrics to groups according to tag value. Tag name goes to group name [telegraf_json]')
            ->prototype('scalar')
            ->defaultValue([])
            ->example(['tag_1']);

        $responder->children()->scalarNode('response_factory')
            ->cannotBeEmpty()
            ->info('Response factory alias')
            ->defaultNull()
            ->example('prometheus');

        $responder->children()->scalarNode('collector')
            ->info('Collector alias')
            ->isRequired()
            ->cannotBeEmpty();
    }
}
