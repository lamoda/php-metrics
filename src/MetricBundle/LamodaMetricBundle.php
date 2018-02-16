<?php

namespace Lamoda\MetricBundle;

use Lamoda\MetricBundle\DependencyInjection\LamodaMetricExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LamodaMetricBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new LamodaMetricExtension();
    }
}
