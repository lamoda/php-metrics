<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Lamoda\Metric\Common\MetricInterface;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Receiver;
use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Source;
use Lamoda\Metric\Storage\MetricReceiverInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
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
        $this->createResponseFactory($responseFactories->prototype('scalar'));

        $responders = $root->children()->arrayNode('responders');
        $responders->useAttributeAsKey('name', false);
        $this->createResponder($responders->prototype('array'));

        $receivers = $root->children()->arrayNode('receivers');
        $receivers->useAttributeAsKey('name', false);
        $this->createReceiver($receivers->prototype('array'));

        $collectors = $root->children()->arrayNode('collectors');
        $collectors->useAttributeAsKey('name', false);
        $this->createCollector($collectors->prototype('array'));

        return $builder;
    }

    private function createSources(ArrayNodeDefinition $source)
    {
        $source->info(
            'Sources also can be configured as services via `' . DefinitionFactory\Source::TAG . '` tag with `' . DefinitionFactory\Source::ALIAS_ATTRIBUTE . '` attribute'
        );
        $source->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(Source::METRIC_SOURCE_TYPES)
            ->info('Type of the source');

        $source->children()
            ->booleanNode('storage')
            ->info('Mark this source as metric storage. Will perform adjustable metrics resolution against it')
            ->defaultFalse();

        $source->children()
            ->scalarNode('id')
            ->defaultNull()
            ->info('Source service identifier');

        $source->children()
            ->scalarNode('entity')
            ->defaultValue(MetricInterface::class)
            ->info('Entity class');

        $source->children()
            ->arrayNode('metrics')
            ->info('Metric services')
            ->defaultValue([])
            ->prototype('scalar')
            ->cannotBeEmpty();
    }

    private function createResponseFactory(ScalarNodeDefinition $responseFactory)
    {
        $responseFactory->info(
            'Response factories also can be configured as services via `' . DefinitionFactory\ResponseFactory::TAG . '` tag with `' . DefinitionFactory\ResponseFactory::ALIAS_ATTRIBUTE . '` attribute'
        );
        $responseFactory->beforeNormalization()->ifString()->then(
            function (string $v) {
                return ['type' => 'service', 'id' => $v];
            }
        );
    }

    private function createCollector(ArrayNodeDefinition $collector)
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
            ->defaultValue(Collector::COLLECTOR_TYPE_SERVICE)
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
            ->arrayNode('tags')
            ->defaultValue([])
            ->info('Append these tags to all collected metrics')
            ->prototype('scalar')
            ->cannotBeEmpty();
    }

    private function createReceiver(ArrayNodeDefinition $receiver)
    {
        $receiver->info(
            'Receivers also can be configured as services via `' . DefinitionFactory\Receiver::TAG . '` tag with `' . DefinitionFactory\Receiver::ALIAS_ATTRIBUTE . '` attribute'
        );
        $receiver->beforeNormalization()->ifString()->then(
            function (string $v) {
                return ['type' => Receiver::RECEIVER_TYPE_SERVICE, 'id' => $v];
            }
        );
        $receiver->canBeDisabled();
        $receiver->children()->scalarNode('id')
            ->cannotBeEmpty()
            ->info('Receiver service ID')
            ->isRequired()
            ->example(MetricReceiverInterface::class);
        $receiver->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue(Receiver::RECEIVER_TYPE_SERVICE)
            ->values(Receiver::TYPES)
            ->info('Type of the receiver');
    }

    private function createResponder(ArrayNodeDefinition $responder)
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
            ->cannotBeEmpty()
            ->example('type');
        $options->children()->scalarNode('group_by_tag')
            ->info('Arrange metrics to groups according to tag value. Tag name goes to group name [telegraf_json]')
            ->defaultNull()
            ->example('tag_1');
        $options->children()->scalarNode('untagged_group_name')
            ->info('Group name for metrics without tag being grouped [telegraf_json]')
            ->defaultNull()
            ->example('other');

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
