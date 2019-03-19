<?php

namespace Lens\Bundle\ApiBundle\Serializer;

use Exception;
use ReflectionClass;

class CircularReferenceHandler
{
    public function __invoke($object)
    {
        if ($object instanceof Exception) {
            return $object->getMessage();
        }

        $reflectionClass = new ReflectionClass($object);
        if (!$reflectionClass->hasProperty('id')) {
            return;
        }

        $reflectionProperty = $reflectionClass->getProperty('id');
        if (!$reflectionProperty->isPublic()) {
            $reflectionProperty->setAccessible(true);
        }

        return $reflectionProperty->getValue($object);
    }
}
