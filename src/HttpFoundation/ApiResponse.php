<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Lens\Bundle\ApiBundle\Hateoas\EmbeddableInterface;
use Lens\Bundle\ApiBundle\Hateoas\EmbeddableTrait;
use Lens\Bundle\ApiBundle\Hateoas\LinkableInterface;
use Lens\Bundle\ApiBundle\Hateoas\LinkableTrait;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response implements LinkableInterface, EmbeddableInterface {
	use LinkableTrait, EmbeddableTrait;

	protected $data = null;

	/**
	 * @param mixed $data    The response data
	 * @param int   $status  The response status code
	 * @param array $headers An array of response headers
	 */
	public function __construct($data = null, $status = 200, $headers = []) {
		parent::__construct('', $status, $headers);

		$this->data = $data;
	}

	/**
	 * Static alias for constructor.
	 *
	 * @param mixed $data    The response data
	 * @param int   $status  The response status code
	 * @param array $headers An array of response headers
	 *
	 * @return ApiResponse
	 */
	public static function create($data = null, $status = 200, $headers = []) {
		return new static($data, $status, $headers);
	}

	/**
	 * Set the Api response data.
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * Returns Api reponse data.
	 */
	public function getData() {
		return $this->data;
	}
}
