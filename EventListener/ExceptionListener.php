<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Exception;
use Lens\Bundle\ApiBundle\Utils\Api;
use Lens\Bundle\ApiBundle\Utils\ContextBuilderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Our exception handler (for catching errors within our api sections).
 */
final class ExceptionListener
{
    private $api;
    private $contextBuilder;
    private $serializer;

    public function __construct(Api $api = null, SerializerInterface $serializer, ContextBuilderInterface $contextBuilder, LoggerInterface $logger)
    {
        $this->api = $api;
        $this->serializer = $serializer;
        $this->contextBuilder = $contextBuilder;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Abort if our request is not an api request (set by hosts/paths in our config).
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        // Get exception and serialize.
        $exception = $event->getThrowable();
        $responseHeaders = $this->api->getResponseHeaders($request);

        if (null !== $this->logger) {
            $this->logger->error('API: Exception listener serializing exception', [
                'exception' => $exception,
            ]);
        }

        // Try using our serializer to format our message.
        $response = null;
        try {
            $mimeType = $this->api->getContentTypeMatch($request)->getType();

            $status = self::getStatusCodeFromException($exception);
            $format = $this->api->getFormatForMimeType($mimeType);
            $context = array_merge_recursive(
                $this->api->serializerDefaultContext(),
                $this->contextBuilder->getContext()
            );

            $normalized = ['status' => $status] + $this->serializer->normalize($exception, $format, $context);
            $serialized = $this->serializer->serialize(['error' => $normalized], $format, $context);

            $response = new Response(
                $serialized,
                $status,
                $responseHeaders
            );
        } catch (Exception $e) {
            // If we had an error trying to serialize just print out a string (since we can't use our serializer).
            $responseHeaders['content-type'] = 'text/plain';

            if (null !== $this->logger) {
                $this->logger->error('API: Exception listener serialization failed.', [
                    'exception' => $e,
                    'target' => $exception,
                ]);
            }

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
