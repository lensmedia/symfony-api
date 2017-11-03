<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

/**
 * Trait to manage embedded resources, implementing EmbeddableInterface.
 */
trait EmbeddableTrait {
	private $_embedded = [];

	public function setEmbedded(string $name, $resource) {
		$this->_embedded[$name] = $resource;
	}

	public function addEmbedded(string $name, $resource, bool $push = false) {
		if (!isset($this->_embedded[$name])) {$this->_embedded[$name] = [];}

		if (!is_array($this->_embedded[$name])) {
			throw new \Exception('Unable to append to embbeded resource list, it is not an array');
		}

		$push ? array_push($this->_embedded[$name], $resource) : $this->_embedded[$name][] = $resource;
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
	}

	public function clearEmbedded(string $name) {
		unset($this->_embedded[$name]);
	}
}
