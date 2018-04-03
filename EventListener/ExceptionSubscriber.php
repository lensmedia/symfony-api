<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Exception;
use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Lens\Bundle\SerializerBundle\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionSubscriber extends AbstractApiEventSubscriber
{
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        $exception = $event->getException();
        $statusCode = $this->getStatusCodeFromException($exception);

        // Test if we can serialize, if we can serialize the exception - otherwise fallback to plain/text output..
        try {
            $data = $this->serializer->serialize($exception, $request->getRequestFormat());

            // If so create a proper response
            $response = new ApiResponse(
                $data,
                $statusCode
            );

            // return an api response with the exception as data, serializer should take care of the rest (see SerializeSubscriber).
            $event->setResponse($response);
        } catch (Exception $e) {
            $event->setResponse(new Response((string) $exception, $statusCode, ['content-type' => 'text/plain']));
        }
    }

    private static function getStatusCodeFromException($exception)
    {
        if (!is_object($exception) || (!$exception instanceof Exception)) {
            return 500;
        }

        if ($exception instanceof AccessDeniedException) {
            return $exception->getCode();
        } elseif ($exception instanceof AuthenticationException) {
            return ApiResponse::HTTP_FORBIDDEN;
        } elseif ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2048],
        ];
    }
}
