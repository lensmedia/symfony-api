<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

use Lens\Bundle\ApiBundle\Hateoas\Link;
use Lens\Bundle\ApiBundle\Hateoas\LinkCollection;
use OutOfRangeException;
use OverflowException;

trait LinkableTrait {
	protected $_links;

	/**
	 * Get a link from our collection by name.
	 *
	 * @param  string $name
	 *
	 * @return Link|null
	 */
	public function getLink(string $name):  ? Link {
		if ($this->hasLink($name)) {
			return $this->_links[$name];
		}
	}

	/**
	 * Adds a new link to our collection.
	 *
	 * @param Link $link
	 *
	 * @return Instance of the class using this trait.
	 */
	public function addLink(Link $link) {
		if ($this->hasLink($name)) {
			throw new OverflowException(sprintf("A link with the name '%s' already exists. Change that object instance instead, or remove it first.", $name));
		}

		$this->_links[] = $link;

		return $this;
	}

	/**
	 * Remove a link from our collection.
	 *
	 * @param  Link|string $link Link object or name.
	 *
	 * @return Instance of the class using this trait.
	 */
	public function removeLink($link) {
		if (!$this->hasLink($link)) {
			throw new OutOfRangeException();
		}

		unset($this->_links[$link]);

		return $this;
	}

	/**
	 * Checks if a specific link/ link name already exists.
	 *
	 * @param  Link|string $link Link object or name.
	 *
	 * @return boolean
	 */
	public function hasLink($link) {
		if (null === $this->_links) {
			$this->_links = new LinkCollection();

			return false;
		}

		return isset($this->_links[$link]);
	}

	/**
	 * Returns our link collection.
	 *
	 * @return LinkCollection
	 */
	public function getLinkCollection() : LinkCollection {
		return (!$this->_links instanceof LinkCollection) ? new LinkCollection() : $this->_links;
	}
}
