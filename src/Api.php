<?php

namespace Lens\Bundle\ApiBundle;

use ArrayObject;
use Negotiation\Accept;
use Negotiation\Negotiator;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Api helper class with common function for internal use.
 */
final class Api implements SerializerInterface, NormalizerInterface
{
    /** @var array An array with supported mime types (key) and their serializer format (value) */
    private array $supportedMimeTypes;

    private Negotiator $negotiator;

    /** @var array Caching array for isApiRequest (skip preg matches on extra calls) */
    private array $entryPointCache = [];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly RequestStack $requestStack,
        private readonly ContextBuilder $contextBuilder,
        private readonly array $options
    ) {
        $this->negotiator = new Negotiator();
    }

    /**
     * Content negotiation helper to get best match from request accept header.
     * This also defaults our api to the to application/json format if nothing
     * else was set.
     */
    public function getContentTypeMatch(?Request $request = null): string
    {
        if (null === $request) {
            $request = $this->requestStack->getCurrentRequest();
        }

        if (null === $request) {
            throw new RuntimeException('getContentTypeMatch can only be used within a request context or using a Request object argument.');
        }

        /** @var Accept $accept */
        $accept = $this->negotiator->getBest(
            $request->headers->get('accept', $this->options['accept']),
            array_keys($this->getSupportedMimeTypes()),
        );

        return $accept?->getType() ?? $this->options['accept'];
    }

    /**
     * Get a api serializer instance (for user access).
     */
    public function getSerializer(): ?SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Get a list of configured mimetypes the api should respond to.
     *
     * @return array array of mime types (key) and their serializer format
     *               (value)
     */
    public function getSupportedMimeTypes(): array
    {
        if (empty($this->supportedMimeTypes)) {
            $this->supportedMimeTypes = [];

            foreach ($this->options['formats'] as $format => $mimes) {
                foreach ($mimes as $mime) {
                    $this->supportedMimeTypes[$mime] = $format;
                }
            }
        }

        return $this->supportedMimeTypes;
    }

    /**
     * Get a serialization format for a specific mimetype.
     */
    public function getFormatForMimeType(string $mime): ?string
    {
        return $this->supportedMimeTypes[$mime] ?? null;
    }

    /**
     * Returns true if the request is an api request (based on configured
     * paths/ hosts).
     */
    public function isApiRequest(Request $request): bool
    {
        return null !== $this->getEntryPoint($request);
    }

    /**
     * Get the first matching configured entry point from our request.
     */
    public function getEntryPoint(Request $request): ?array
    {
        if (empty($this->options['entry_points'])) {
            return null;
        }

        $requestHash = spl_object_hash($request);
        if (!isset($this->entryPointCache[$requestHash])) {
            foreach ($this->options['entry_points'] as $entry) {
                $host = $this->checkApiRequest($request->getHost(), $entry['host']);
                $path = $this->checkApiRequest($request->getPathInfo(), $entry['path']);

                if (!empty($entry['host']) && !empty($entry['path']) && $host && $path) {
                    $this->entryPointCache[$requestHash] = $entry;
                    break;
                }

                if (!empty($entry['host']) && $host) {
                    $this->entryPointCache[$requestHash] = $entry;
                    break;
                }

                if (!empty($entry['path']) && $path) {
                    $this->entryPointCache[$requestHash] = $entry;
                    break;
                }
            }
        }

        return $this->entryPointCache[$requestHash] ?? null;
    }

    /**
     * Internal helper function to check for a regex pattern in an array of
     * strings.
     *
     * @return bool true if a match is found, false if none are found
     */
    private function checkApiRequest(string $targetString, array $regexes): bool
    {
        foreach ($regexes as $regex) {
            if (preg_match('@'.str_replace('@', '\@', $regex).'@i', $targetString)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate response headers based on our entry point settings.
     */
    public function getResponseHeaders(Request $request): array
    {
        $defaults = [
            'access-control-allow-origin' => '*',
            'access-control-allow-credentials' => 'true',
            'access-control-allow-methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'access-control-allow-headers' => 'content-type, authorization, accept, origin',
        ];

        // Add content type from our current request format.
        $contentType = $this->getContentTypeMatch($request);
        if ($contentType) {
            $defaults['content-type'] = $contentType;
        }

        // Add our entry point data..
        $entry = $this->getEntryPoint($request);

        return array_merge($defaults, $this->options['headers'], $entry['headers']);
    }

    public function serialize($data, ?string $format = null, array $context = []): string
    {
        if (!$format) {
            $contentType = $this->getContentTypeMatch();
            $format = $this->getFormatForMimeType($contentType);
        }

        return $this->serializer->serialize(
            $data,
            $format,
            $this->contextBuilder->getContext($context),
        );
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|ArrayObject|bool|float|int|null|string
    {
        return $this->serializer->normalize(
            $data,
            $format,
            $this->contextBuilder->getContext($context),
        );
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->serializer->supportsNormalization($data, $format);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
