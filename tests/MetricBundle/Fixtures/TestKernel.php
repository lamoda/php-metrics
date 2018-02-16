<?php

namespace Lamoda\MetricBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Lamoda\MetricBundle\LamodaMetricBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    public function __construct($environment, $debug)
    {
        $fs = new Filesystem();
        $fs->remove($this->getCacheDir());
        $fs->remove($this->getLogDir());
        parent::__construct($environment, $debug);
    }

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
