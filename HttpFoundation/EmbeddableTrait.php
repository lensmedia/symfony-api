<?php
namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Doctrine\Common\Collections\Collection;

/**
 * Trait to manage embedded resources, implementing EmbeddableInterface.
 */
trait EmbeddableTrait {
	private $_embedded = [];

	/**
	 * @param  string $name     Group name for this embedded entry.
	 * @param  mixed  $resource The resource to embed.
	 * @return self
	 */
	public function setEmbedded(string $name, $resource) {
		$this->_embedded[$name] = $resource;

		return $this;
	}

	/**
	 * Adds an item to an embedded collection.
	 *
	 * @param  string $name     The name of our collection.
	 * @param  mixed  $resource The resource to embed.
	 * @param  bool   $merge    If resource is an array merge it instead of appending it as a single item.
	 * @return self
	 */
	public function addEmbedded(string $name, $resource, bool $merge = true) {
		if (!isset($this->_embedded[$name])) {
			$this->_embedded[$name] = [];
		}

		if (!is_array($this->_embedded[$name])) {
			throw new \Exception('Unable to append to embbeded resource list, it is not an array');
		}

		// If we have a doctrine collection, convert it to array so we can merge.
		if ($resource instanceof Collection) {
			$resource = $resource->toArray();
		}

		// If we can merge and it is enabled merge the array with existing stuff, otherwise append it.
		if (is_array($resource) && false !== $merge) {
			$this->_embedded[$name] = array_merge($this->_embedded[$name], $resource);
		} else {
			$this->_embedded[$name][] = $resource;
		}

		return $this;
	}

	/**
	 * Test if a specific entry already contains a specific resource.
	 *
	 * @param  string      $name
	 * @param  $resource
	 * @return bool
	 */
	public function hasEmbedded(string $name, $resource) {
		if (!isset($this->_embedded[$name])) {
			return false;
		}

		if (!is_array($this->_embedded[$name])) {
			return $this->_embedded[$name] === $resource;
		}

		return in_array($this->_embedded[$name], $resource, true);
	}

	/**
	 * Returns all embedded resources (should be avoided).
	 *
	 * @return mixed
	 */
	public function getEmbedded() {
		return $this->_embedded;
	}

	/**
	 * Remove a resource from a specific entry.
	 * @param  string $name
	 * @param  mixed  $resource
	 * @return self
	 */
	public function removeEmbedded(string $name, $resource) {
		if (!$this->hasEmbedded($name, $resource)) {
			return;
		}

		if (is_array($this->_embedded[$name])) {
			unset($this->_embedded[$name][array_search($this->_embedded[$name], $resource, true)]);
		} else {
			unset($this->_embedded[$name]);
		}

		return $this;
	}

	/**
	 * Clears all embedded resources.
	 *
	 * @param  string $name
	 * @return self
	 */
	public function clearEmbedded(string $name = null) {
		if (null === $name) {
			$this->_embedded = [];
		} else {
			unset($this->_embedded[$name]);
		}

		return $this;
	}
}
