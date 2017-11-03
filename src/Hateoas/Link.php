<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

use Lens\Bundle\ApiBundle\Serializer\Normalizer\AbstractApiNormalizer;

/**
 * HATEOAS Link class, currently not supporting curies yet.
 */
class Link {
	/**
	 * @var string The link name eg. self, next, previous, etc..
	 */
	private $name;

	/**
	 * @var string The href for this link, this should be relative but can be whatever you like.
	 */
	private $href;

	/**
	 * Create a link.
	 *
	 * @param string|null $name
	 * @param string|null $href
	 */
	public function __construct(string $name, string $href) {
		$this->setName($name);
		$this->setHref($href);
	}

	/**
	 * Static alias for creating a link.
	 *
	 * @param  string $name
	 * @param  string $href
	 *
	 * @return Link
	 */
	public static function create(string $name, string $href) {
		return new static($name, urldecode($href));
	}

	/**
	 * Set the name.
	 *
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the href.
	 *
	 * @param string $href
	 */
	public function setHref(string $href) {
		$this->href = $href;

		return $this;
	}

	/**
	 * Get the href.
	 *
	 * @return string
	 */
	public function getHref() {
		return $this->href;
	}
}
