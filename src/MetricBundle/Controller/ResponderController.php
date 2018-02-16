<?php

namespace Lamoda\MetricBundle\Controller;

use Lamoda\MetricResponder\MetricGroupSourceInterface;
use Lamoda\MetricResponder\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class ResponderController
{
    /**
     * @var MetricGroupSourceInterface
     */
    private $source;
    /**
     * @var ResponseFactoryInterface
     */
    private $factory;
    /**
     * @var string
     */
    private $prefix;

    public function __construct(
        MetricGroupSourceInterface $source,
        ResponseFactoryInterface $factory,
        string $prefix = ''
    ) {
        $this->source = $source;
        $this->factory = $factory;
        $this->prefix = $prefix;
    }

    public function __invoke(): Response
    {
        $response = $this->factory->create($this->source, $this->prefix);
        $symfonyResponse = new Response(
            (string) $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );

        $symfonyResponse->setPrivate();

        return $symfonyResponse;
    }
}
