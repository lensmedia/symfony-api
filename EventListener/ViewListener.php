<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Utils\Api;
use Lens\Bundle\ApiBundle\Utils\ContextBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Our view listener intercepts any return from controllers that are not instances/inherited of a Response class.
 *
 * In here we convert any data to a response for it to be serialized and returned.
 */
final class ViewListener
{
    private $api;
    private $contextBuilder;

    public function __construct(Api $api = null, ContextBuilderInterface $contextBuilder)
    {
        $this->api = $api;
        $this->contextBuilder = $contextBuilder;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $controllerResult = $event->getControllerResult();
        if ($controllerResult instanceof Response) {
            $event->setResponse($controllerResult);

            return;
        }

        $contentType = $this->api->getContentTypeMatch($request)->getType();
        $context = array_merge_recursive(
            $this->api->serializerDefaultContext(),
            $this->contextBuilder->getContext()
        );

        $headers = $this->api->getResponseHeaders($request);

        $content = $this->api->getSerializer()->serialize(
            $controllerResult,
            $this->api->getFormatForMimeType($contentType),
            $context
        );

        $response = new Response($content, Response::HTTP_OK, $headers);

        $event->setResponse($response);
    }
}
