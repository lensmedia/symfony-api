<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizers;

use Lens\Bundle\ApiBundle\HttpFoundation\Link;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkCollection;
use Lens\Bundle\SerializerBundle\Serializer\Normalizer\DenormalizerInterface;
use Lens\Bundle\SerializerBundle\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes api link collection.
 */
class LinkCollectionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($data, string $format = null, array $context = [])
    {
        $output = [];
        foreach ($data as $key => $link) {
            $output[$key]['href'] = $link->getHref();
            $output[$key] += $link->getContext();
        }

        return $output;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof LinkCollection;
    }

    public function denormalize($data, string $class, string $format, array $context = [])
    {
        if (!is_array($data)) {
            return new LinkCollection();
        }

        $collection = new LinkCollection();
        foreach ($data as $key => $link) {
            $collection[] = Link::create($key, $data[$key]['href']);
        }

        return $collection;
    }

    public function supportsDenormalization($data, string $class, string $format = null, array $context = []): bool
    {
        return (LinkCollection::class === $type) || is_subclass_of($data, LinkCollection::class);
    }
}
