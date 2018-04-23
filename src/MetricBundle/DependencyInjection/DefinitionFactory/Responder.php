<?php

namespace Lamoda\Metric\MetricBundle\DependencyInjection\DefinitionFactory;

final class Responder
{
    const ID_PREFIX = 'lamoda_metrics.responder_controller.';

    public static function createId(string $name): string
    {
        return self::ID_PREFIX . $name;
    }
}
