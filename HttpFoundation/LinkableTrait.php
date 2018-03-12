<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use OutOfRangeException;
use OverflowException;

trait LinkableTrait
{
    protected $_links;

    /**
     * Get a link from our collection by name.
     *
     * @param string $name
     *
     * @return Link|null
     */
    public function getLink(string $name): ? Link
    {
        if ($this->hasLink($name)) {
            return $this->_links[$name];
        }
    }

    /**
     * Adds a new link to our collection.
     *
     * @param Link $link
     *
     * @return $this
     */
    public function addLink(Link $link)
    {
        if ($this->hasLink($link)) {
            throw new OverflowException(sprintf("A link with the name '%s' already exists. Change that object instance instead, or remove it first.", $name));
        }

        $this->_links[] = $link;

        return $this;
    }

    /**
     * Remove a link from our collection.
     *
     * @param Link|string $link link object or name
     *
     * @return $this
     */
    public function removeLink($link)
    {
        if (!$this->hasLink($link)) {
            throw new OutOfRangeException();
        }

        unset($this->_links[$link]);

        return $this;
    }

    /**
     * Checks if a specific link/ link name already exists.
     *
     * @param Link|string $link link object or name
     *
     * @return bool
     */
    public function hasLink($link)
    {
        if (null === $this->_links) {
            $this->_links = new LinkCollection();

            return false;
        }

        if ($link instanceof Link) {
            $link = $link->getName();
        }

        return isset($this->_links[$link]);
    }

    /**
     * Removes all links from the collection.
     *
     * @return $this
     */
    public function clearLinks()
    {
        $this->_links = new LinkCollection();

        return $this;
    }

    /**
     * Returns our link collection.
     *
     * @return LinkCollection
     */
    public function getLinkCollection(): LinkCollection
    {
        return (!$this->_links instanceof LinkCollection) ? new LinkCollection() : $this->_links;
    }
}
