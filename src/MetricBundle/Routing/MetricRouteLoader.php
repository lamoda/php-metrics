<?php

namespace Lamoda\MetricBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class MetricRouteLoader implements LoaderInterface
{
    private $controllers = [];

    private $loaded = false;

    /**
     * Add controllers on path for registration.
     *
     * @param string $path
     * @param string $serviceId
     *
     * @throws \LogicException
     */
    public function registerController(string $path, string $serviceId)
    {
        if (array_key_exists($path, $this->controllers)) {
            throw new \LogicException('Cannot register metric controller on the same path twice');
        }

        $this->controllers[$path] = $serviceId;
    }

    /** {@inheritdoc} */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new \LogicException('Lamoda metrics routes have been already loaded');
        }

        $collection = new RouteCollection();

        foreach ($this->controllers as $path => $controller) {
            $collection->add($controller, new Route($path, ['_controller' => $controller]));
        }

        $this->loaded = true;

        return $collection;
    }

    /** {@inheritdoc} */
    public function supports($resource, $type = null)
    {
        return 'lamoda_metrics' === $type;
    }

    /** {@inheritdoc} */
    public function getResolver()
    {
    }

    /** {@inheritdoc} */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
}
