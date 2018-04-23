<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class Receiver
{
    const TAG = 'lamoda_metrics.receiver';
    const ALIAS_ATTRIBUTE = 'alias';
    const ID_PREFIX = 'lamoda_metrics.receiver.';

    const REGISTRY_ID = 'lamoda_metrics.receiver_registry';

    const RECEIVER_TYPE_SERVICE = 'service';
    const RECEIVER_TYPE_DOCTRINE = 'doctrine';

    const TYPES = [
        self::RECEIVER_TYPE_SERVICE,
        self::RECEIVER_TYPE_DOCTRINE,
    ];

    public static function createId(string $name): string
    {
        return self::ID_PREFIX . $name;
    }

    public static function createReference(string $name): Reference
    {
        return new Reference(self::createId($name));
    }

    public static function register(ContainerBuilder $container, string $name, array $config)
    {
        switch ($config['type']) {
            case self::RECEIVER_TYPE_SERVICE:
                $container->getDefinition(self::REGISTRY_ID)->addMethodCall(
                    'register',
                    [$name, self::createReference($name)]
                );
                $container->setAlias(self::createId($name), $config['id']);
                break;
            case self::RECEIVER_TYPE_DOCTRINE:
                break;
        }
    }
}
