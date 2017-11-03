<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

class Resource implements EmbeddableInterface {
	use EmbeddableTrait;

	protected $data = null;

	/**
	 * @param mixed $data
	 */
	public function __construct($data = null) {
		$this->setData($data);
	}

	/**
	 * Set the Api response data.
	 */
	public function setData($data = null) {
		$this->data = $data;
	}

	/**
	 * Returns Api reponse data.
	 */
	public function getData() {
		return $this->data;
	}
}
