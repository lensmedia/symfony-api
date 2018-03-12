<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizers;

use Lens\Bundle\ApiBundle\HttpFoundation\Link;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes api link collection.
 */
class LinkCollectionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($data, $format = null, array $context = [])
    {
        $output = [];
        foreach ($data as $key => $link) {
            $output[$key]['href'] = $link->getHref();
            $output[$key] += $link->getContext();
        }

        return $output;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LinkCollection;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
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

    public function supportsDenormalization($data, $type, $format = null)
    {
        return (LinkCollection::class === $type) || is_subclass_of($data, LinkCollection::class);
    }
}
