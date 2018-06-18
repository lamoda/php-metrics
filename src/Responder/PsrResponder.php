<?php

namespace Lamoda\Metric\Responder;

use Lamoda\Metric\Collector\MetricCollectorInterface;
use Psr\Http\Message\ResponseInterface;

final class PsrResponder
{
    /**
     * @var MetricCollectorInterface
     */
    private $collector;
    /**
     * @var ResponseFactoryInterface
     */
    private $factory;
    /**
     * @var array
     */
    private $options;

    public function __construct(
        MetricCollectorInterface $collector,
        ResponseFactoryInterface $factory,
        array $options = []
    ) {
        $this->collector = $collector;
        $this->factory = $factory;
        $this->options = $options;
    }

    /**
     * Create PSR-7 HTTP response for collected metrics
     *
     * @return ResponseInterface
     */
    public function createResponse(): ResponseInterface
    {
        return $this->factory->create($this->collector->collect(), $this->options);
    }
}
