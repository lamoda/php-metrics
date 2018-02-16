<?php

namespace Lamoda\MetricResponder;

use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * Create preformatted response from metric group source with given metric prefix.
     *
     * @param MetricGroupSourceInterface $source
     * @param string $prefix
     *
     * @return ResponseInterface
     */
    public function create(MetricGroupSourceInterface $source, string $prefix = ''): ResponseInterface;
}
