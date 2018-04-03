<?php

namespace Lens\Bundle\ApiBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Lens\Bundle\ApiBundle\HttpFoundation\EmbeddableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\ResourceTrait;
use Lens\Bundle\SerializerBundle\Serializer\Normalizer\NormalizerInterface;
use Lens\Bundle\SerializerBundle\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class ResourceNormalizer implements NormalizerInterface, LinkableInterface, EmbeddableInterface
{
    use ResourceTrait;

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var bool
     */
    protected $hateoas = false;

    protected $serializer;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, RouterInterface $router, EntityManagerInterface $manager, Serializer $serializer)
    {
        // *note* serializer has to be done using the serializer aware interface
        //        getting it from the container results in a circular reference error.
        $this->container = $container;
        $this->router = $router;
        $this->entityManager = $manager;
        $this->serializer = $serializer;
        $this->hateoas = $container->getParameter('lens_api.hateoas') ?? false;
    }

    /**
     * @param object $object  Object to normalize
     * @param string $format  Format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return mixed
     */
    public function normalize($object, $format = null, array $context = [])
    {
        // Clear links and embedded (this is required to reset for each call as its a new entity)
        $this->clearLinks();
        $this->clearEmbedded();

        // Call process function, this is also where one can add links etc.
        if ($object instanceof ApiResponse) {
            $context = $object->getContext() + $context;
        }

        $data = $this->process($object, $format, $context);

        // Check if we have links & embedded.
        $links = $this->getLinkCollection();
        $embedded = $this->getEmbedded();

        // If we do not have an array we return the plain data.
        if (!is_array($data)) {
            // If we did have some links or embedded convert our data value into an array
            // so we can still append the links and embedded resources.
            // Otherwise just return the data as value.
            if ((count($links) && $this->isHateoas()) || count($embedded)) {
                if (null !== $data) {
                    $data = ['data' => $data];
                }
            } else {
                return $data;
            }
        }

        $output = [];

        // Add links to our output.
        if (count($links) && $this->isHateoas()) {
            $output = array_merge_recursive($output, ['_links' => $this->serializer->normalize($links, $format, $context)]);
        }

        // Add data.
        if (is_array($data)) {
            $output += $data;
        }

        // Add embedded resources.
        if (count($embedded)) {
            $items = $this->serializer->normalize($embedded, $format, $context);

            $output = array_merge_recursive($output, $this->isHateoas() ? ['_embedded' => $items] : $items);
        }

        return $output;
    }

    /**
     * @return bool
     */
    protected function isHateoas()
    {
        return $this->hateoas;
    }

    /**
     * New 'normalize' function for inherited normalize classess, normalize interface is handled here in this abstract class.
     *
     * Allows for a more natural flow (no need to do things with parent as links and embedded resources are handled here).
     *
     * @param object $object  Object to normalize
     * @param string $format  Format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return mixed
     */
    abstract public function process($data, string $format = null, array $context = []);

    /**
     * @param $data   Mixed  data to test if it supported
     * @param $format Format the normalization result will be encoded as
     */
    abstract public function supportsNormalization($data, string $format = null, array $context = []): bool;
}
