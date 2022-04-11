<?php

namespace Lamoda\Metric\MetricBundle;

use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterCollectorsPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterReceiversPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterResponseFactoriesPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\LamodaMetricExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
final class LamodaMetricBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new LamodaMetricExtension();
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterReceiversPass());
        $container->addCompilerPass(new RegisterCollectorsPass());
        $container->addCompilerPass(new RegisterResponseFactoriesPass());
    }
}
