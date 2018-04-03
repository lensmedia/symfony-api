<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response implements LinkableInterface, EmbeddableInterface
{
    use ResourceTrait;

    protected $data = null;
    protected $context = [];

    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($data = null, int $status = 200, array $headers = [], array $context = [])
    {
        parent::__construct('', $status, $headers);

        $this->context = $context;
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
    public static function create($data = null, $status = 200, $headers = [], $context = [])
    {
        return new static($data, $status, $headers, $context);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Embed alias for collection resources (with count, offset and limit).
     */
    public function embedCollection(string $name, $resource, bool $merge = true)
    {
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

            $this->embed($name, $resource, $merge);
        }

        return $this;
    }

    public function group(string ...$groups)
    {
        if (!isset($this->context['groups'])) {
            $this->context['groups'] = [];
        }

        $this->context['groups'] = array_merge($this->context['groups'], $groups);

        return $this;
    }

    protected function isCountable($data)
    {
        return is_array($data) || is_object($data) && $data instanceof \Countable;
    }
}
