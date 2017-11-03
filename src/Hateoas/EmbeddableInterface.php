<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

/**
 * Manage embedded resources.
 */
interface EmbeddableInterface {
	/**
	 * Sets a named collection to a specific resource.
	 *
	 * @param string $name
	 * @param  mixed $resource
	 */
	function setEmbedded(string $name, $resource);

	/**
	 * Adds a resource to an already existsing or new collection of resources.
	 *
	 * @param string $name
	 * @param mixed $resource
	 *
	 * @param bool $push Set true to enable the resource to be pushed to the embedded collection instead of appending.
	 */
	function addEmbedded(string $name, $resource, bool $push = false);

	/**
	 * Checks if a resource already is in the named collection.
	 *
	 * @param string $name
	 * @param  mixed $resource
	 */
	function hasEmbedded(string $name, $resource);

	/**
	 * Returns all embedded resources.
	 */
	function getEmbedded();

	/**
	 * Remove a specific resource from a named collection.
	 *
	 * @param  string $name     Collection name
	 * @param   mixed $resource
	 */
	function removeEmbedded(string $name, $resource);

	/**
	 * Clear an entire embbeded named collection.
	 *
	 * @param  string $name
	 */
	function clearEmbedded(string $name);

}
