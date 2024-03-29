<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Api;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class ErrorListener
{
    public const IGNORE_LISTENER = 'listener';
    public const IGNORE_LOGGER = 'logger';

    public function __construct(
        private readonly Api $api,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
        private readonly array $excludedErrors = [],
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $error = $event->getThrowable();

        /** @see AuthenticationFailedListener wraps these exceptions, mostly... */
        if ($error instanceof AccessDeniedException) {
            return;
        }

        if ($this->isExcluded($error, self::IGNORE_LISTENER)) {
            return;
        }

        // Request stack is still empty in this listener so pushing
        // the current one so it can be used later in the serializer.
        $this->requestStack->push($event->getRequest());

        $status = self::getStatusCodeFromError($error);
        $responseHeaders = $this->getResponseHeaders($request, $error);

        // Try using our serializer to format our message.
        try {
            $normalized = ['status' => $status] + $this->api->normalize($error);
            $serialized = $this->api->serialize($normalized);

            if (!$this->isExcluded($error, self::IGNORE_LOGGER)) {
                $this->logger?->error(sprintf(
                    'API: Serialized error: %s',
                    $error->getMessage(),
                ), context: [
                    'error' => $error,
                    'headers' => $responseHeaders->all(),
                ]);
            }

            $response = new Response($serialized, $status, $responseHeaders->all());
        } catch (Throwable $e) {
            // If we had an error trying to serialize just print out a string (since we can't use our serializer).
            $responseHeaders['content-type'] = 'text/plain';

            if (!$this->isExcluded($error, self::IGNORE_LOGGER)) {
                $this->logger?->error('API: Error listener serialization failed.', [
                    'error' => $e,
                    'target' => $error,
                ]);
            }

            $response = new Response((string)$error, $status, $responseHeaders->all());
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
        }

        if ($error instanceof AccessDeniedException) {
            return Response::HTTP_FORBIDDEN;
        }

        if ($error instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        return 500;
    }

    /**
     * Test our options array to see if the specified class is excluded.
     */
    private function isExcluded(Throwable $object, string $state): bool
    {
        if (empty($this->excludedErrors)) {
            return false;
        }

        $class = get_class($object);

        return in_array($class, $this->excludedErrors[$state], true);
    }

    private function getResponseHeaders(Request $request, Throwable $object): ResponseHeaderBag
    {
        $responseHeaders = $this->api->getResponseHeaders($request);

        if ($object instanceof HttpExceptionInterface) {
            $responseHeaders = array_merge($responseHeaders, $object->getHeaders());
        }

        return new ResponseHeaderBag($responseHeaders);
    }
}
