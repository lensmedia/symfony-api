<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizer;

use Lens\Bundle\ApiBundle\Exception\CustomContextHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Throwable;

class ErrorNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @param Throwable $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $output = [];

        $output['code'] = $object->getCode();
        $output['message'] = empty($object->getMessage())
            ? null
            : $object->getMessage();

        if ($object instanceof CustomContextHttpException) {
            $output = array_merge($output, $object->getContext());
        }

        // Do not do anything else if it is not debug.
        if (($context['debug'] ?? false) !== true) {
            return $output;
        }

        if ($object instanceof HttpException) {
            $output['headers'] = $object->getHeaders();
        }

        $output['file'] = $object->getFile();
        $output['line'] = $object->getLine();
        $output['trace'] = $object->getTraceAsString();

        $previous = $object->getPrevious();
        if ($previous) {
            $output['previous'] = $this->serializer->normalize(
                $previous,
                $format,
                $context,
            );
        }

        return $output;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Throwable;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            CustomContextHttpException::class => true,
            'object' => false,
        ];
    }
}
