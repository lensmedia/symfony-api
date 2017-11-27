<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber extends AbstractApiEventSubscriber {
	public function onKernelException(GetResponseForExceptionEvent $event) {
		$request = $event->getRequest();
		if (!$this->isApiRequest($request)) {
			return;
		}

		$exception = $event->getException();

		$response = new ApiResponse($exception, $exception instanceof HttpException ? $exception->getStatusCode() : 500);

		// return an api response with the exception as data, serializer should take care of the rest (see SerializeSubscriber).
		$event->setResponse($response);
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::EXCEPTION => ['onKernelException', 2048],
		];
	}
}
