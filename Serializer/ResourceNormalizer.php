<?php

namespace Lens\Bundle\ApiBundle\Serializer;

use Lens\Bundle\ApiBundle\HttpFoundation\EmbeddableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\EmbeddableTrait;
use Lens\Bundle\ApiBundle\HttpFoundation\Link;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkableTrait;
use Lens\Bundle\ApiBundle\HttpFoundation\ResourceTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

abstract class ResourceNormalizer implements SerializerAwareInterface, NormalizerInterface, LinkableInterface, EmbeddableInterface {
	use ResourceTrait, SerializerAwareTrait;

	protected $container;
	protected $router;
	protected $entityManager;

	public function __construct(ContainerInterface $container) {
		// *note* serializer has to be done using the serializer aware interface
		//        getting it from the container results in a circular reference error.
		$this->container     = $container;
		$this->router        = $container->get('router');
		$this->entityManager = $container->get('doctrine.orm.entity_manager');
	}

	public function normalize($object, $format = null, array $context = []) {
		// Clear links and embedded (this is required to reset for each call as its a new entity)
		$this->clearLinks();
		$this->clearEmbedded();

		// Call process function, this is also where one can add links etc.
		$data = $this->process($object, $format, $context);

		// Check if we have links & embedded.
		$links    = $this->getLinkCollection();
		$embedded = $this->getEmbedded();

		// If we do not have an array we return the plain data.
		if (!is_array($data)) {
			// If we did have some links or embedded convert our data value into an array
			// so we can still append the links and embedded resources.
			// Otherwise just return the data as value.
			if ((count($links) && $context['hateoas']) || count($embedded)) {
				$data = ['data' => $data];
			} else {
				return $data;
			}
		}

		$output = [];

		// Add links to our output.
		if (count($links) && $context['hateoas']) {
			$output = array_merge_recursive($output, ['_links' => $this->serializer->normalize($links, $format, $context)]);
		}

		// Add data.
		if (is_array($data)) {
			$output += $data;
		}

		// Add embedded resources.
		if (count($embedded)) {
			$items = $this->serializer->normalize($embedded, $format, $context);

			$output = array_merge_recursive($output, $context['hateoas'] ? ['_embedded' => $items] : $items);
		}

		return $output;
	}

	abstract public function supportsNormalization($data, $format = null);

	/**
	 * New 'normalize' function for inherited normalize classess, normalize interface is handled here in this abstract class.
	 *
	 * Allows for a more natural flow (no need to do things with parent as links and embedded resources are handled here).
	 *
	 * @param  object      $object  The object to normalize.
	 * @param  string|null $format  The format (json, yaml, etc.)
	 * @param  array       $context
	 *
	 * @return mixed
	 */
	abstract public function process($object, $format = null, array $context = []);
}
