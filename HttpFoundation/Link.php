<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

/**
 * HATEOAS Link class, currently not supporting curies yet.
 */
class Link
{
    /**
     * @var string The link name eg. self, next, previous, etc..
     */
    private $name;

    /**
     * @var string the href for this link, this should be relative but can be whatever you like
     */
    private $href;

    /**
     * @var array key value pair for extra context to be serialized
     */
    private $context = [];

    /**
     * Static alias for creating a link.
     *
     * @param string $name
     * @param string $href
     *
     * @return Link
     */
    public static function create(string $name, string $href, array $context = [])
    {
        return (new static())
            ->setName($name)
            ->setHref($href)
            ->setContext($context);
    }

    /**
     * Set the name.
     *
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the href.
     *
     * @param string $href
     */
    public function setHref(string $href): self
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get the href.
     *
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
