<?php

namespace Lamoda\Metric\MetricBundle\Controller;

use Lamoda\Metric\Responder\PsrResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class HttpFoundationResponder
{
    /** @var PsrResponder */
    private $psrResponder;

    /**
     * ResponderController constructor.
     *
     * @param PsrResponder $psrResponder
     */
    public function __construct(PsrResponder $psrResponder)
    {
        $this->psrResponder = $psrResponder;
    }

    /**
     * Create HTTP-Kernel response for collected
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function createResponse(): Response
    {
        try {
            $response = $this->psrResponder->createResponse();

            $symfonyResponse = new Response(
                (string) $response->getBody(),
                $response->getStatusCode(),
                $response->getHeaders()
            );
        } catch (\Exception $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, null, $exception);
        }

        $symfonyResponse->setPrivate();

        return $symfonyResponse;
    }
}
