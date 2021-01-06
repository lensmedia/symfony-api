<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Throwable;

/**
 * Normalize thrown errors to an array.
 */
class ErrorNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    public function normalize($error, string $format = null, array $context = [])
    {
        $output = [];

        $output['code'] = $error->getCode();
        $output['message'] = empty($error->getMessage())
            ? null
            : $error->getMessage();

        if (isset($context['debug']) && $context['debug']) {
            $output['file'] = $error->getFile();
            $output['line'] = $error->getLine();
            $output['trace'] = $error->getTraceAsString();

            $previous = $error->getPrevious();
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

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Throwable;
    }
}
