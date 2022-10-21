<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Error;
use Lens\Bundle\ApiBundle\Api;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class ErrorListener
{
    const IGNORE_LISTENER = 'listener';
    const IGNORE_LOGGER = 'logger';

    public function __construct(
        private Api $api,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
        private array $excludedErrors = [],
    ) {
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $error = $event->getThrowable();

        if ($this->isExcluded($error, self::IGNORE_LISTENER)) {
            return;
        }

        // Request stack is still empty in this listener so pushing
        // the current one so it can be used later in the serializer.
        $this->requestStack->push($event->getRequest());

        // Get error and serialize.
        $responseHeaders = $this->api->getResponseHeaders($request);

        // Try using our serializer to format our message.
        $response = null;
        try {
            $status = self::getStatusCodeFromError($error);

            $normalized = ['status' => $status] + $this->api->normalize($error);
            $serialized = $this->api->serialize(['error' => $normalized]);

            if (!$this->isExcluded($error, self::IGNORE_LOGGER)) {
                $this->logger?->error(
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

            if (!$this->isExcluded($error, self::IGNORE_LOGGER)) {
                $this->logger?->error('API: Error listener serialization failed.', [
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
    private static function getStatusCodeFromError(?Throwable $error = null): int
    {
        if ($error instanceof HttpExceptionInterface) {
            return $error->getStatusCode();
        } elseif ($error instanceof AccessDeniedException) {
            return $error->getCode();
        } elseif ($error instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        } else {
            return 500;
        }
    }

    /**
     * Test our options array to see if the specified class is excluded.
     */
    private function isExcluded(throwable $object, string $state): bool
    {
        if (empty($this->excludedErrors)) {
            return false;
        }

        $class = is_string($object)
            ? $object
            : get_class($object);

        return in_array($class, $this->excludedErrors[$state]);
    }
}
