<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\Compiler;

use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Storage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterReceiversPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(Storage::REGISTRY_ID);

        $services = $container->findTaggedServiceIds(Storage::TAG);
        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes[Storage::ALIAS_ATTRIBUTE])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Missing "%s" attribute for "%s" tag "%s"',
                            Storage::ALIAS_ATTRIBUTE,
                            $id,
                            Storage::TAG
                        )
                    );
                }

                $registry->addMethodCall('register', [$attributes[Storage::ALIAS_ATTRIBUTE], new Reference($id)]);
            }
        }
    }
}
