<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelEvents;
use Lens\Bundle\ApiBundle\Controller\ApiController;
use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    public function __construct(ContainerInterface $container, SerializerInterface $serializer)
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
        if (!$this->isApiRequest($request)) {
            return;
        }

        // Get our response and check if it is an api response.
        $response = $event->getResponse();
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
        $context = [
            'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            'hateoas' => $this->container->getParameter('lens_api.hateoas'),
        ];

        if (true === $this->container->getParameter('lens_api.exclusive')) {
            $context['groups'] = ['default'];
        }

        $content = $this->serializer->serialize($data, $request->getRequestFormat(), $context);

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
