<?php

namespace Lens\Bundle\ApiBundle\Utils;

use Negotiation\Accept;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Api helper class with common function for internal use.
 */
final class Api
{
    /** @var array yaml configured options */
    private $options;

    /** @var Serializer serializer to be used */
    private $serializer;

    /** @var array An array with supported mime types (key) and their serializer format (value) */
    private $supportedMimeTypes;

    /** @var Negotiator instance */
    private $negotiator;

    /** @var array Caching array for isApiRequest (skip preg matches on extra calls) */
    private $entryPointCache = [];

    /** @var bool are we in dev environment */
    private $dev = false;

    public function __construct(array $options, $serializer = null, bool $dev = false)
    {
        $this->options = $options;
        $this->serializer = $serializer;
        $this->dev = $dev;

        $this->negotiator = new Negotiator();
    }

    public function serializerDefaultContext()
    {
        return $this->options['serializer']['default_context'];
    }

    /**
     * Content negotiation helper to get best match from request accept header.
     * This also defaults our api to the to application/json format if nothing else was set.
     *
     * @return Accept
     */
    public function getContentTypeMatch(Request $request)
    {
        $accept = $request->headers->has('accept')
            ? $request->headers->get('accept')
            : $this->options['accept'];

        return $this->negotiator->getBest(
            $accept,
            array_keys($this->getSupportedMimeTypes())
        );
    }

    /**
     * Get a serializer instance.
     *
     * @return SerializerInterface|null
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get a list of configured mimetypes the api should respond to.
     *
     * @return array array of mime types (key) and their serializer format (value)
     */
    public function getSupportedMimeTypes(): array
    {
        if (null === $this->supportedMimeTypes) {
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
    public function getFormatForMimeType(string $mime): string
    {
        if (isset($this->supportedMimeTypes[$mime])) {
            return $this->supportedMimeTypes[$mime];
        }

        return null;
    }

    /**
     * Returns true if the request is an api request (based on configured paths/ hosts).
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
            $found = false;
            foreach ($this->options['entry_points'] as $entry) {
                $host = $this->checkApiRequest($request->getHost(), $entry['host']);
                $path = $this->checkApiRequest($request->getPathInfo(), $entry['path']);

                if (!empty($entry['host']) && !empty($entry['path']) && $host && $path) {
                    $found = true;
                    break;
                }

                if (!empty($entry['host']) && $host) {
                    $found = true;
                    break;
                }

                if (!empty($entry['path']) && $path) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->entryPointCache[$requestHash] = $entry;
            }
        }

        return isset($this->entryPointCache[$requestHash]) ? $this->entryPointCache[$requestHash] : null;
    }

    /**
     * Are we in development environment.
     */
    public function isDev(): bool
    {
        return $this->dev;
    }

    /**
     * Internal helper function to check for a regex pattern in an array of strings.
     *
     * @param string $request
     * @param array  $options
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
     *
     * @return
     */
    public function getResponseHeaders(Request $request): array
    {
        $defaults = [
            'access-control-allow-origin' => '*',
            'access-control-allow-credentials' => true,
            'access-control-allow-methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'access-control-allow-headers' => 'content-type, authorization, accept, origin',
        ];

        // Add content type from our current request format.
        $contentType = $this->getContentTypeMatch($request);
        if ($contentType) {
            $defaults['content-type'] = $contentType->getType();
        }

        // Add our entry point data..
        $entry = $this->getEntryPoint($request);

        return array_merge($defaults, $this->options['headers'], $entry['headers']);
    }
}
