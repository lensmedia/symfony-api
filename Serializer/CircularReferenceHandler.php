<?php

namespace Lens\Bundle\ApiBundle\Serializer;

use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class CircularReferenceHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($object)
    {
        if ($object instanceof Exception) {
            return $object->getMessage();
        }

        // Check for id property on object and return its value.
        $reflectionClass = new ReflectionClass($object);
        if (!$reflectionClass->hasProperty('id')) {
            $this->logger->warning(static::class.' was triggered but no ID property fallback was available (returned null).', [
                'object' => $object,
            ]);

            return null;
        }

        $reflectionProperty = $reflectionClass->getProperty('id');
        if (!$reflectionProperty->isPublic()) {
            $reflectionProperty->setAccessible(true);
        }

        return $reflectionProperty->getValue($object);
    }
}
