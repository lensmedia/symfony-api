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

    public function normalize($object, string $format = null, array $context = []): array
    {
        $output = [];

        $output['code'] = $object->getCode();
        $output['message'] = empty($object->getMessage())
            ? null
            : $object->getMessage();

        if (isset($context['debug']) && $context['debug']) {
            $output['file'] = $object->getFile();
            $output['line'] = $object->getLine();
            $output['trace'] = $object->getTraceAsString();

            $previous = $object->getPrevious();
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

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Throwable;
    }
}
