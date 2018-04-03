<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizers;

use Lens\Bundle\SerializerBundle\Serializer\Normalizer\NormalizerInterface;
use Lens\Bundle\SerializerBundle\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionNormalizer implements NormalizerInterface
{
    protected $container;
    protected $serializer;

    public function __construct(ContainerInterface $container, Serializer $serializer)
    {
        $this->container = $container;
        $this->serializer = $serializer;
    }

    public function normalize($exception, string $format = null, array $context = [])
    {
        $dev = $this->container->getParameter('kernel.debug');

        $output = [];
        $output['code'] = (0 === $exception->getCode()) ? ($exception instanceof HttpException ? $exception->getStatusCode() : 0) : $exception->getCode();

        // if ($dev) {
        //     $output['exception'] = get_class($exception);
        // }

        $output['message'] = $exception->getMessage();

        // if (($exception instanceof ApiException) && (!empty($exception->getData()))) {
        //     $output['data'] = $exception->getData();
        // }

        // if ($dev) {
        //     $output['file'] = $exception->getFile();
        //     $output['line'] = $exception->getLine();
        //     $output['trace'] = $exception->getTrace();
        //     $output['previous'] = $this->serializer->normalize($exception->getPrevious(), $format, $context);
        // }

        return $output;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \Exception;
    }
}
