<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizer;

use Exception;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalize thrown exceptions to an array.
 */
class ExceptionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    public function normalize($exception, $format = null, array $context = [])
    {
        $output = [];

        $output['code'] = $exception->getCode();
        $output['message'] = empty($exception->getMessage()) ? null : $exception->getMessage();

        if (isset($context['debug']) && $context['debug']) {
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

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Exception;
    }
}
