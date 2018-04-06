<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Exception;
use Lens\Bundle\ApiBundle\Utils\Api;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Our exception handler (for catching errors within our api sections).
 */
final class ExceptionListener
{
    private $api;

    public function __construct(Api $api = null)
    {
        $this->api = $api;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        // Get exception and serialize.
        $exception = $event->getException();

        $responseHeaders = $this->api->getResponseHeaders($request);
        $mimeType = $this->api->getContentTypeMatch($request)->getType();

        // Try using our serializer to format our message.
        $response = null;
        try {
            $content = $this->api->getSerializer()->serialize(
                $exception,
                $this->api->getFormatForMimeType($mimeType)
            );

            $response = new Response(
                $content,
                self::getStatusCodeFromException($exception),
                $responseHeaders
            );
        } catch (Exception $e) {
            // If we had an error trying to serialize just print out a string (since we can't use our serializer).
            $responseHeaders['content-type'] = 'text/plain';

            $response = new Response(
                (string) $exception,
                self::getStatusCodeFromException($exception),
                $responseHeaders
            );
        }

        $event->setResponse($response);
    }

    /**
     * Get our HTTP status code from our exception. Some built in exceptions don't have any associated status code or implement it differently.
     *
     * @param Exception $exception
     *
     * @return int
     */
    private static function getStatusCodeFromException(Exception $exception = null)
    {
        if (null === $exception) {
            return 500;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        } elseif ($exception instanceof AccessDeniedException) {
            return $exception->getCode();
        } elseif ($exception instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        return 500;
    }
}
