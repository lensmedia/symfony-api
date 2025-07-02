<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Api;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Set Symfony's internal request format by doing content negotiation.
 */
final class ContentNegotiationListener
{
    public function __construct(
        private readonly Api $api,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $match = $this->api->getContentTypeMatch($request);
        $format = $this->api->getFormatForMimeType($match);
        $request->setRequestFormat($format);
    }
}
