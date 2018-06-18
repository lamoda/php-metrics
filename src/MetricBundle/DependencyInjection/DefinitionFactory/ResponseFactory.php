<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class ResponseFactory
{
    public const TAG = 'lamoda_metrics.response_factory';
    public const ALIAS_ATTRIBUTE = 'alias';

    public const TYPES = [
        self::FACTORY_TYPE_SERVICE,
    ];

    private const FACTORY_TYPE_SERVICE = 'service';

    private const ID_PREFIX = 'lamoda_metrics.response_factory.';

    public static function createId(string $name): string
    {
        return self::ID_PREFIX . $name;
    }

    public static function createReference(string $name): Reference
    {
        return new Reference(self::createId($name));
    }

    public static function register(ContainerBuilder $container, string $name, array $config): void
    {
        switch ($config['type']) {
            case self::FACTORY_TYPE_SERVICE:
                $container->setAlias(self::createId($name), $config['id']);
                break;
        }
    }
}
