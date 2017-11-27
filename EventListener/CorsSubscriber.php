<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber extends AbstractApiEventSubscriber {
	public function onKernelResponse(FilterResponseEvent $event) {
		$request = $event->getRequest();
		if (!$this->isApiRequest($request)) {
			return;
		}

		// Set allow origin to all
		$event
			->getResponse()
			->headers
			->set('Access-Control-Allow-Origin', '*');
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::RESPONSE => ['onKernelResponse', -4096],
		];
	}
}
