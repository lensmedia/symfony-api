<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

interface LinkableInterface {
	/**
	 * Gets a specific named link
	 *
	 * @param  string $name The name of the link (eg. self)
	 *
	 * @return Link
	 */
	public function getLink(string $name);

	/**
	 * Adds a link to our collection
	 *
	 * @param  Link $link
	 */
	public function addLink(Link $link);

	/**
	 * Removes a link by name or object.
	 *
	 * @param  string|Link $link
	 */
	public function removeLink($link);

	/**
	 * Checks if a link name specified by string or Link object is already exists.
	 *
	 * @param  string|Link $link
	 *
	 * @return boolean
	 */
	public function hasLink($link);

	/**
	 * Removes all links from the collection.
	 *
	 * @return $this
	 */
	public function clearLinks();

	/**
	 * Returns our link collection object.
	 *
	 * @return LinkCollection
	 */
	public function getLinkCollection();
}
