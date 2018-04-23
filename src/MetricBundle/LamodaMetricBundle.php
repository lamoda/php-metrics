<?php

namespace Lamoda\Metric\MetricBundle;

use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterCollectorsPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterReceiversPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\Compiler\RegisterResponseFactoriesPass;
use Lamoda\Metric\MetricBundle\DependencyInjection\LamodaMetricExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LamodaMetricBundle extends Bundle
{
    public function getContainerExtension()
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
