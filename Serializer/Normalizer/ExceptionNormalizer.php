<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizer;

use Lens\Bundle\ApiBundle\Utils\Api;
use Lens\Bundle\SerializerBundle\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize thrown exceptions to an array.
 */
class ExceptionNormalizer implements NormalizerInterface
{
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * Normalize our exception (code and message) and extra fields if in dev environment.
     *
     * @param Exception   $exception
     * @param string|null $format
     * @param array       $context
     *
     * @return array
     */
    public function normalize($exception, string $format = null, array $context = [])
    {
        $data = [];
        $data['code'] = $exception->getCode();
        $data['message'] = $exception->getMessage();

        if ($this->api->isDev()) {
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTrace();
            $data['previous'] = $this->api->getSerializer()->normalize($exception->getPrevious(), $format, $context);
        }

        return $data;
    }

    /**
     * Check if supplied data is an Exception.
     *
     * @param mixed       $data
     * @param string|null $format
     * @param array       $context
     *
     * @return bool
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return is_object($data) && $data instanceof \Exception;
    }
}
