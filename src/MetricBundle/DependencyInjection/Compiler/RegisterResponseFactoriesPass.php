<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\Compiler;

use Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory\ResponseFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterResponseFactoriesPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds(ResponseFactory::TAG);
        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes[ResponseFactory::ALIAS_ATTRIBUTE])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Missing "%s" attribute for "%s" tag "%s"',
                            ResponseFactory::ALIAS_ATTRIBUTE,
                            $id,
                            ResponseFactory::TAG
                        )
                    );
                }

                $container->setAlias(ResponseFactory::createId($attributes[ResponseFactory::ALIAS_ATTRIBUTE]), $id);
            }
        }
    }
}
