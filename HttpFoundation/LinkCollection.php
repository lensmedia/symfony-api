<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Collection class for Links.
 *
 * Primary use for this is in combination with the serializer (LinkCollectionNormalizer).
 */
class LinkCollection implements ArrayAccess, IteratorAggregate, Countable
{
    protected $container = [];

    // ArrayAccess
    public function offsetSet($offset, $link)
    {
        if (!$link instanceof Link) {
            throw new InvalidArgumentException(sprintf('Value has to be a string or of type %s', Link::class));
        }

        $this->container[$link->getName()] = $link;
    }

    public function offsetExists($offset)
    {
        $offset = $this->checkOffset($offset);

        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        $offset = $this->checkOffset($offset);
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        $offset = $this->checkOffset($offset);

        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    protected function checkOffset($offset)
    {
        // If we have a link, convert it to an offset.
        if ($offset instanceof Link) {
            $offset = $offset->getName();
        }

        // If the offset is still not a string, we have an invalid argument.
        if (!is_string($offset)) {
            throw new InvalidArgumentException(sprintf('Offset has to be a string or of type %s', Link::class));
        }

        return $offset;
    }

    // IteratorAggregate
    public function getIterator()
    {
        return new ArrayIterator($this->container);
    }

    // Countable
    public function count()
    {
        return count($this->container);
    }
}
