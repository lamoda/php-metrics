<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\Compiler;

use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\Receiver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterReceiversPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(Receiver::REGISTRY_ID);

        $services = $container->findTaggedServiceIds(Receiver::TAG);
        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes[Receiver::ALIAS_ATTRIBUTE])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Missing "%s" attribute for "%s" tag "%s"',
                            Receiver::ALIAS_ATTRIBUTE,
                            $id,
                            Receiver::TAG
                        )
                    );
                }

                $registry->addMethodCall('register', [$attributes[Receiver::ALIAS_ATTRIBUTE], new Reference($id)]);
            }
        }
    }
}
