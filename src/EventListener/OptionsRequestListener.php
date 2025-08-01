<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Api;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Set Symfony's internal request format by doing content negotiation.
 */
final class OptionsRequestListener
{
    public function __construct(
        private readonly Api $api,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if ('options' !== strtolower($request->getMethod()) || !$this->api->isApiRequest($request)) {
            return;
        }

        $response = new Response(
            null,
            Response::HTTP_NO_CONTENT,
            $this->api->getResponseHeaders($request),
        );

        $event->setResponse($response);
    }
}
