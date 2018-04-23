<?php

namespace Lamoda\Metric\MetricBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Lamoda\Metric\MetricBundle\LamodaMetricBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    /** {@inheritdoc} */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new LamodaMetricBundle(),
            new TestIntegrationBundle(),
        ];
    }

    /** {@inheritdoc} */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../../../build/cache';
    }

    public function getLogDir()
    {
        return __DIR__ . '/../../../build/logs';
    }
}
