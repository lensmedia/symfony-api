<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Api;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * Our view listener intercepts any return from controllers that are not
 * instances/inherited of a Response class.
 *
 * In here we convert any data to a response for it to be serialized and returned.
 */
final class ViewListener
{
    public function __construct(
        private readonly Api $api,
    ) {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $headers = $this->api->getResponseHeaders($request);

        $controllerResult = $event->getControllerResult();
        if (null === $controllerResult) {
            $response = new Response(null, Response::HTTP_NO_CONTENT, $headers);
            $event->setResponse($response);

            return;
        }

        $response = new Response(
            $this->api->serialize($controllerResult),
            Response::HTTP_OK,
            $headers
        );

        $event->setResponse($response);
    }
}
