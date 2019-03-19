<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizer;

use Exception;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalize thrown exceptions to an array.
 */
final class ExceptionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * Normalize our exception (code and message) and extra fields if in debug.
     */
    public function normalize($exception, $format = null, array $context = array())
    {
        $output = [];

        $output['code'] = $exception->getCode();
        $output['type'] = get_class($exception);
        $output['message'] = empty($exception->getMessage()) ? null : $exception->getMessage();

        if ($context['debug']) {
            $output['file'] = $exception->getFile();
            $output['line'] = $exception->getLine();
            $output['trace'] = $exception->getTraceAsString();

            $previous = $exception->getPrevious();
            if ($previous) {
                $output['previous'] = $this->serializer->normalize(
                    $previous,
                    $format,
                    $context
                );
            }
        }

        return $output;
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
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Exception;
    }
}
