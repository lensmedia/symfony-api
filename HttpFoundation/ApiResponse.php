<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Lens\Bundle\ApiBundle\HttpFoundation\EmbeddableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\LinkableInterface;
use Lens\Bundle\ApiBundle\HttpFoundation\ResourceTrait;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response implements LinkableInterface, EmbeddableInterface {
	use ResourceTrait;

	protected $data    = null;
	protected $context = [];

	/**
	 * @param mixed $data    The response data
	 * @param int   $status  The response status code
	 * @param array $headers An array of response headers
	 */
	public function __construct($data = null, $status = 200, $headers = [], $context = []) {
		parent::__construct('', $status, $headers);

		$this->context = $context;
		$this->data    = $data;
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
	public static function create($data = null, $status = 200, $headers = [], $context = []) {
		return new static($data, $status, $headers, $context);
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

	public function setContext($context) {
		$this->context = $context;
	}

	public function getContext() {
		return is_array($this->context) ? $this->context : [];
	}

	/**
	 * Embed alias for collection resources (with count, offset and limit).
	 */
	public function embedCollection(string $name, $resource, int $offset = null, int $limit = null) {
		if (null !== $resource) {
			if (!$this->isCountable($resource)) {
				throw new \Exception(sprintf("To embed a collection it has to be an array or implement Countable, got '%s'", gettype($name)));
			}

			if ($resource instanceof Paginator) {
				$this->data['total'] = count($resource);
				$this->data['count'] = count($resource->getIterator());
				$this->data['limit'] = $resource->getQuery()->getMaxResults();
			} else {
				$this->data['count'] = count($resource);
			}

			$this->embed($name, $resource);
		}

		return $this;
	}

	protected function isCountable($data) {
		return is_array($data) || is_object($data) && $data instanceof \Countable;
	}
}
