<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Utils\Api;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Set symfonys internal request format by doing content negotiation.
 */
final class ContentNegotiationListener
{
    private $api;

    public function __construct(Api $api = null)
    {
        $this->api = $api;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        // Get our best match, if we have any set our request format.
        $match = $this->api->getContentTypeMatch($request);
        if (null !== $match) {
            $format = $this->api->getFormatForMimeType($match->getType());
            $request->setRequestFormat($format);
        }
    }
}
