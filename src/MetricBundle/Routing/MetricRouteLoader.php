<?php

namespace Lamoda\Metric\MetricBundle\Routing;

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
     * @param string $name
     * @param string $path
     * @param string $serviceId
     * @param string $method
     *
     * @throws \LogicException
     */
    public function registerController(string $name, string $path, string $serviceId, string $method = '__invoke')
    {
        if (array_key_exists($path, $this->controllers)) {
            throw new \LogicException('Cannot register metric controller on the same path twice');
        }

        $this->controllers[$name] = [$path, $serviceId, $method];
    }

    /** {@inheritdoc} */
    public function load($resource, $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new \LogicException('Lamoda metrics routes have been already loaded');
        }

        $collection = new RouteCollection();

        foreach ($this->controllers as $name => list($path, $controller)) {
            $collection->add($controller, new Route($path, ['_controller' => $controller]));
        }

        $this->loaded = true;

        return $collection;
    }

    /** {@inheritdoc} */
    public function supports($resource, $type = null): bool
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
