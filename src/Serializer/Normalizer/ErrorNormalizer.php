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
     * @param Throwable $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $output = [];

        $output['code'] = $data->getCode();
        $output['message'] = empty($data->getMessage())
            ? null
            : $data->getMessage();

        if ($data instanceof CustomContextHttpException) {
            $output = array_merge($output, $data->getContext());
        }

        // Do not do anything else if it is not debug.
        if (($context['debug'] ?? false) !== true) {
            return $output;
        }

        if ($data instanceof HttpException) {
            $output['headers'] = $data->getHeaders();
        }

        $output['file'] = $data->getFile();
        $output['line'] = $data->getLine();
        $output['trace'] = $data->getTraceAsString();

        $previous = $data->getPrevious();
        if ($previous) {
            $output['previous'] = $this->serializer->normalize(
                $previous,
                $format,
                $context,
            );
        }

        return $output;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
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
