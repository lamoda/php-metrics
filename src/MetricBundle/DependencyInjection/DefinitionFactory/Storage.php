<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class Storage
{
    public const TAG = 'lamoda_metrics.storage';
    public const ALIAS_ATTRIBUTE = 'alias';
    public const REGISTRY_ID = 'lamoda_metrics.storage_registry';

    public const TYPES = [
        self::STORAGE_TYPE_SERVICE,
    ];

    private const STORAGE_TYPE_SERVICE = 'service';
    private const ID_PREFIX = 'lamoda_metrics.storage.';

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
            case self::STORAGE_TYPE_SERVICE:
                $container->setAlias(self::createId($name), $config['id']);
                break;
        }

        $container->getDefinition(self::REGISTRY_ID)->addMethodCall('register', [$name, self::createReference($name)]);
    }
}
