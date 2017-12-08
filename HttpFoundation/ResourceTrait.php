<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait ResourceTrait {
	use LinkableTrait, EmbeddableTrait;

	protected $count;

	/**
	 * Shorthand helper alias for adding links.
	 */
	public function link(string $name, string $href) {
		$link = Link::create($name, $href);

		return $this->addLink($link);
	}

	/**
	 * Shorthand helper alias for adding embedded resources.
	 */
	public function embed(string $name, $resource, bool $merge = true) {
		$this->addEmbedded($name, $resource, $merge);

		return $this;
	}

	/**
	 * Generates a URL from the given parameters.
	 *
	 * @param string $route         The name of the route
	 * @param mixed  $parameters    An array of parameters
	 * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		return $this->container->get('router')->generate($route, $parameters, $referenceType);
	}

	/**
	 * Checks if the attributes are granted against the current authentication token and optionally supplied object.
	 *
	 * @param mixed $attributes The attributes
	 * @param mixed $object     The object
	 *
	 * @return bool
	 *
	 * @throws \LogicException
	 */
	public function isGranted($attributes, $object = null) {
		if (!$this->container->has('security.authorization_checker')) {
			throw new \LogicException('The SecurityBundle is not registered in your application.');
		}

		return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
	}

	/**
	 * Get current user
	 *
	 * @return User
	 */
	protected function getUser() {
		if (!$this->container->has('security.token_storage')) {
			throw new \LogicException('The SecurityBundle is not registered in your application.');
		}

		if (null === $token = $this->container->get('security.token_storage')->getToken()) {
			return;
		}

		if (!is_object($user = $token->getUser())) {
			return;
		}

		return $user;
	}

	/**
	 * Match route.
	 */
	public function matchRoute($routes) {
		if (!is_array($routes)) {
			$routes = (array) $routes;
		}

		// Get current route from request match
		$currentRoute = $this->container
		                     ->get('request_stack')
		                     ->getCurrentRequest()
		                     ->get('_route');

		return in_array($currentRoute, $routes);
	}
}
