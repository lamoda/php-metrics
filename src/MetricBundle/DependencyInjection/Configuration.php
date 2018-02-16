<?php

namespace Lamoda\MetricBundle\DependencyInjection;

use Lamoda\MetricResponder\MetricGroupInterface;
use Lamoda\MetricResponder\MetricInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** {@inheritdoc} */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('lamoda_metrics');
        $root->addDefaultsIfNotSet();

        $metrics = $root->children()->arrayNode('metrics');
        $metrics->addDefaultsIfNotSet();
        $this->configureMetricSourcesNode($metrics->children()->arrayNode('sources'));

        $groups = $root->children()->arrayNode('groups');
        $groups->addDefaultsIfNotSet();
        $this->configureGroupSourcesNode($groups->children()->arrayNode('sources'));
        $this->configureCustomGroupsNode($groups->children()->arrayNode('custom'));

        $responders = $root->children()->arrayNode('responders');
        $responders->useAttributeAsKey('name', false);
        $this->createResponder($responders->prototype('array'));

        return $builder;
    }

    private function createResponder(ArrayNodeDefinition $responder)
    {
        $responder->canBeEnabled();
        $responder->children()->scalarNode('path')
            ->cannotBeEmpty()
            ->info('Responder route path')
            ->defaultNull()
            ->example('/telegraf');

        $responder->children()->scalarNode('prefix')
            ->info('Metrics prefix for responder')
            ->defaultValue('')
            ->example('project_name_');

        $responder->children()->scalarNode('response_factory')
            ->cannotBeEmpty()
            ->info('Response factory service ID')
            ->defaultNull()
            ->example('lamoda_metrics.response_factory.telegraf');

        $responder->children()->arrayNode('sources')
            ->prototype('scalar')
            ->info('Group sources for responder controller')
            ->defaultValue([]);

        $responder->children()->arrayNode('groups')
            ->prototype('scalar')
            ->info('Groups for responder controller')
            ->defaultValue([]);
    }

    /**
     * @param ArrayNodeDefinition $parent
     */
    private function configureMetricSourcesNode(ArrayNodeDefinition $parent)
    {
        $parent->useAttributeAsKey('name');
        $parent->defaultValue([]);
        $parent->requiresAtLeastOneElement();

        /** @var ArrayNodeDefinition $prototype */
        $prototype = $parent->prototype('array');
        $prototype->children()
            ->enumNode('type')
            ->cannotBeEmpty()
            ->defaultValue('service')
            ->values(MetricSourceDefinitionFactory::METRIC_SOURCE_TYPES)
            ->info('Type of the source');

        $prototype->children()
            ->booleanNode('storage')
            ->info('Mark this source as metric storage. Will perform adjustable metrics resolution against it')
            ->defaultFalse();

        $prototype->children()
            ->scalarNode('id')
            ->defaultNull()
            ->info('Source service identifier');

        $prototype->children()
            ->scalarNode('entity')
            ->defaultValue(MetricInterface::class)
            ->info('Entity class');

        $prototype->children()
            ->arrayNode('metrics')
            ->info('Metric services')
            ->defaultValue([])
            ->prototype('scalar')
            ->cannotBeEmpty();
    }

    private function configureGroupSourcesNode(ArrayNodeDefinition $parent)
    {
        $parent->useAttributeAsKey('name');
        $parent->defaultValue([]);
        $parent->requiresAtLeastOneElement();

        /** @var ArrayNodeDefinition $prototype */
        $prototype = $parent->prototype('array');
        $prototype->children()
            ->enumNode('type')
            ->defaultValue('service')
            ->values(MetricGroupSourceDefinitionFactory::METRIC_GROUP_SOURCE_TYPES)
            ->info('Type of the source');

        $prototype->children()
            ->booleanNode('storage')
            ->info('Mark this source as metric storage. Will perform adjustable metrics resolution against it')
            ->defaultFalse();

        $prototype->children()
            ->scalarNode('id')
            ->defaultNull()
            ->info('Service identifier');

        $prototype->children()
            ->scalarNode('entity')
            ->defaultValue(MetricGroupInterface::class)
            ->info('Entity class');

        $prototype->children()
            ->arrayNode('groups')
            ->info('Group services')
            ->defaultValue([])
            ->prototype('scalar')
            ->cannotBeEmpty();
    }

    private function configureCustomGroupsNode(ArrayNodeDefinition $parent)
    {
        $parent->useAttributeAsKey('name');
        $parent->defaultValue([]);
        $parent->requiresAtLeastOneElement();

        /** @var ArrayNodeDefinition $prototype */
        $prototype = $parent->prototype('array');
        $prototype->children()
            ->arrayNode('tags')
            ->ignoreExtraKeys(false)
            ->defaultValue([])
            ->info('Group tags')
            ->prototype('scalar')
            ->cannotBeEmpty();

        $prototype->children()
            ->booleanNode('storage')
            ->info('Mark this source as metric storage. Will perform adjustable metrics resolution against it')
            ->defaultFalse();

        $prototype->children()
            ->arrayNode('metric_sources')
            ->defaultValue([])
            ->info('Metric source names or service ids')
            ->prototype('scalar');

        $prototype->children()
            ->arrayNode('metric_services')
            ->defaultValue([])
            ->info('Additional metric services for this group (also populated with tag)')
            ->prototype('scalar');
    }
}
