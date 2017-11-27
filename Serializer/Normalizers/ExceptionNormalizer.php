<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizers;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface {
	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function normalize($object, $format = null, array $context = []) {
		$dev = $this->container->getParameter('kernel.debug');

		$output         = [];
		$output['code'] = 0 === $object->getCode() ? ($object instanceof HttpException ? $object->getStatusCode() : 0) : $object->getCode();
		if ($dev) {$output['exception'] = get_class($object);}

		$output['message'] = $object->getMessage();

		if (($object instanceof ApiException) && (!empty($object->getData()))) {
			$output['data'] = $object->getData();
		}

		if ($dev) {
			$output['file']     = $object->getFile();
			$output['line']     = $object->getLine();
			$output['trace']    = $object->getTrace();
			$output['previous'] = $this->container->get('serializer')->normalize($object->getPrevious(), $format, $context);
		}

		return $output;
	}

	public function supportsNormalization($data, $format = null) {
		return $data instanceof \Exception;
	}
}
