<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use InvalidArgumentException;
use Lens\Bundle\ApiBundle\Controller\ApiController;
use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Lens\Bundle\SerializerBundle\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SerializeSubscriber extends AbstractApiEventSubscriber
{
    /**
     * @var mixed
     */
    protected $container;

    /**
     * @var mixed
     */
    protected $serializer;

    /**
     * @param ContainerInterface  $container
     * @param SerializerInterface $serializer
     */
    public function __construct(ContainerInterface $container, Serializer $serializer)
    {
        $this->container = $container;
        $this->serializer = $serializer;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        if (!$this->isApiRequest($request, $response)) {
            return;
        }

        // Get our response and check if it is an api response.
        if (!$response instanceof ApiResponse) {
            // Allow file responses
            if ($response instanceof BinaryFileResponse) {
                return;
            }

            throw new InvalidArgumentException(sprintf(
                "Invalid response detected in controller '%s', all responses in an %s should be of type %s",
                $request->get('_controller'),
                ApiController::class,
                ApiResponse::class
            ));

            return;
        }

        // Get data and set object root index.
        $index = $response->getData() instanceof \Exception ? 'error' : 'data';
        $data = [$index => $response];

        // Serialize
        $content = $this->serializer->serialize(
            $data,
            $request->getRequestFormat(),
            $response->getContext()
        );

        $response->setContent($content);
        $response->headers->set('content-type', 'application/json');
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -4096],
        ];
    }
}
