<?php

namespace Lamoda\Metric\Responder;

use Lamoda\Metric\Common\MetricSourceInterface;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * Create preformatted response from metric source with given metric prefix.
     *
     * @param MetricSourceInterface $source
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function create(MetricSourceInterface $source, array $options = []): ResponseInterface;
}
