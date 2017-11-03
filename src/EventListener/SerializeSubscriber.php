<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use InvalidArgumentException;
use Lens\Bundle\ApiBundle\Controller\ApiController;
use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeSubscriber extends AbstractApiEventSubscriber {
	protected $serializer;

	public function __construct(SerializerInterface $serializer) {
		$this->serializer = $serializer;
	}

	public function onKernelResponse(FilterResponseEvent $event) {
		$request = $event->getRequest();
		if (!$this->isApiRequest($request)) {
			return;
		}

		// Get our response and check if it is an api response.
		$response = $event->getResponse();
		if (!$response instanceof ApiResponse) {
			throw new InvalidArgumentException(sprintf(
				"Invalid response detected in controller '%s', all responses in an %s should be of type %s",
				$request->get('_controller'),
				ApiController::class,
				ApiResponse::class
			));

			return;
		}

		// Get data and set object root index.
		$data  = $response->getData();
		$index = $data instanceof \Exception ? 'error' : 'data';

		// Serialize
		$content = $this->serializer->serialize(
			[$index => $data],
			$request->getRequestFormat(),
			['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES]
		);

		$response->setContent($content);
		$response->headers->set('content-type', 'application/json');
	}

	public static function getSubscribedEvents() {
		return [
			KernelEvents::RESPONSE => ['onKernelResponse', -4096],
		];
	}
}
