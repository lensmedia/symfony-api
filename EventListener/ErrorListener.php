<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Error;
use Lens\Bundle\ApiBundle\Api;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class ErrorListener
{
    private Api $api;
    private LoggerInterface $logger;

    public function __construct(
        Api $api,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        // Get error and serialize.
        $error = $event->getThrowable();
        $responseHeaders = $this->api->getResponseHeaders($request);

        // Try using our serializer to format our message.
        $response = null;
        try {
            $status = self::getStatusCodeFromError($error);

            $normalized = ['status' => $status] + $this->api->normalize($error);
            $serialized = $this->api->serialize(['error' => $normalized]);

            if (null !== $this->logger) {
                $this->logger->error(
                    sprintf('API: Serialized error: %s', $error->getMessage()),
                    ['error' => $error]
                );
            }

            $response = new Response(
                $serialized,
                $status,
                $responseHeaders
            );
        } catch (Error $e) {
            // If we had an error trying to serialize just print out a string (since we can't use our serializer).
            $responseHeaders['content-type'] = 'text/plain';

            if (null !== $this->logger) {
                $this->logger->error('API: Error listener serialization failed.', [
                    'error' => $e,
                    'target' => $error,
                ]);
            }

            $response = new Response(
                (string) $error,
                self::getStatusCodeFromError($error),
                $responseHeaders
            );
        }

        $event->setResponse($response);
    }

    /**
     * Get our HTTP status code from our error.
     * Some built in errors don't have any associated status
     * code or implement it differently.
     */
    private static function getStatusCodeFromError(Throwable $error = null)
    {
        if (null === $error) {
            return 500;
        }

        if ($error instanceof HttpExceptionInterface) {
            return $error->getStatusCode();
        } elseif ($error instanceof AccessDeniedException) {
            return $error->getCode();
        } elseif ($error instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        return 500;
    }
}
