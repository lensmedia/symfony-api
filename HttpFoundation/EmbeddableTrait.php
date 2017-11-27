<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Doctrine\Common\Collections\Collection;

/**
 * Trait to manage embedded resources, implementing EmbeddableInterface.
 */
trait EmbeddableTrait {
	private $_embedded = [];

	public function setEmbedded(string $name, $resource) {
		$this->_embedded[$name] = $resource;

		return $this;
	}

	public function addEmbedded(string $name, $resource, bool $merge = true) {
		if (!isset($this->_embedded[$name])) {$this->_embedded[$name] = [];}

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

	public function hasEmbedded(string $name, $resource) {
		if (!isset($this->_embedded[$name])) {return false;}

		if (!is_array($this->_embedded[$name])) {
			return $this->_embedded[$name] === $resource;
		}

		return in_array($this->_embedded[$name], $resource);
	}

	public function getEmbedded() {
		return $this->_embedded;
	}

	public function removeEmbedded(string $name, $resource) {
		if (!$this->hasEmbedded($name, $resource)) {
			return;
		}

		if (is_array($this->_embedded[$name])) {
			unset($this->_embedded[$name][array_search($this->_embedded[$name], $resource)]);
		} else {
			unset($this->_embedded[$name]);
		}

		return $this;
	}

	public function clearEmbedded(string $name = null) {
		if (null === $name) {
			$this->_embedded = [];
		} else {
			unset($this->_embedded[$name]);
		}

		return $this;
	}
}
