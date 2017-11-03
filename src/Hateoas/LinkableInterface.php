<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

interface LinkableInterface {
	/**
	 * Gets a specific named link
	 *
	 * @param  string $name The name of the link (eg. self)
	 *
	 * @return Link
	 */
	function getLink(string $name);

	/**
	 * Adds a link to our collection
	 *
	 * @param  Link $link
	 */
	function addLink(Link $link);

	/**
	 * Removes a link by name or object.
	 *
	 * @param  string|Link $link
	 */
	function removeLink($link);

	/**
	 * Checks if a link name specified by string or Link object is already exists.
	 *
	 * @param  string|Link $link
	 *
	 * @return bool
	 */
	function hasLink($link);

	/**
	 * Returns our link collection object.
	 *
	 * @return LinkCollection
	 */
	function getLinkCollection();
}
