<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * *WIP* Performs content negotiation.
 */
class ContentNegotiationSubscriber extends AbstractApiEventSubscriber {
	public function onKernelResponse(FilterResponseEvent $event) {
		$request = $event->getRequest();
		if (!$this->isApiRequest($request)) {
			return;
		}

		// No negotiation yet, just flatout JSON..
		// If we do want to implement this at some point:
		// https://github.com/willdurand/Negotiation, works like magic.
		$request->setRequestFormat('json');
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::RESPONSE => ['onKernelResponse', -2048],
		];
	}
}
