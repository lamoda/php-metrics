<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\Compiler;

use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Collector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterCollectorsPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(Collector::REGISTRY_ID);

        $services = $container->findTaggedServiceIds(Collector::TAG);
        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes[Collector::ALIAS_ATTRIBUTE])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Missing "%s" attribute for "%s" tag "%s"',
                            Collector::ALIAS_ATTRIBUTE,
                            $id,
                            Collector::TAG
                        )
                    );
                }

                $registry->addMethodCall('register', [$attributes[Collector::ALIAS_ATTRIBUTE], new Reference($id)]);
            }
        }
    }
}
