<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

/**
 * Manage embedded resources.
 */
interface EmbeddableInterface
{
    /**
     * Sets a named collection to a specific resource.
     *
     * @param string $name
     * @param mixed  $resource
     */
    public function setEmbedded(string $name, $resource);

    /**
     * Adds a resource to an already existsing or new collection of resources.
     *
     * @param string $name
     * @param mixed  $resource
     * @param bool   $push     Set true to enable the resource to be pushed to the embedded collection instead of appending.
     */
    public function addEmbedded(string $name, $resource, bool $push = false);

    /**
     * Checks if a resource already is in the named collection.
     *
     * @param string $name
     * @param mixed  $resource
     */
    public function hasEmbedded(string $name, $resource);

    /**
     * Returns all embedded resources.
     */
    public function getEmbedded();

    /**
     * Remove a specific resource from a named collection.
     *
     * @param string $name     Collection name
     * @param mixed  $resource
     */
    public function removeEmbedded(string $name, $resource);

    /**
     * Clear an entire embbeded named collection.
     *
     * @param string $name
     */
    public function clearEmbedded(string $name);
}
