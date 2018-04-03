<?php

namespace Lens\Bundle\ApiBundle\Serializer\Normalizers;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Lens\Bundle\ApiBundle\Serializer\ResourceNormalizer;

class ApiResponseNormalizer extends ResourceNormalizer
{
    public function process($apiResponse, string $format = null, array $context = [])
    {
        // Convert reponse links to ApiNormalizer links (internal)
        foreach ($apiResponse->getLinkCollection() as $link) {
            $this->addLink($link);
        }

        // Same for embedded resources
        foreach ($apiResponse->getEmbedded() as $key => $embedded) {
            $this->addEmbedded($key, $embedded);
        }

        // Return the resource data
        $context = array_merge_recursive($context, $apiResponse->getContext());

        return $this->serializer->normalize($apiResponse->getData(), $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof ApiResponse;
    }
}
