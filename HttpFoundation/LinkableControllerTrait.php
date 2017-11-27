<?php

namespace Lens\Bundle\ApiBundle\HttpFoundation;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Linkable controller trait, helper function for adding links from within a controller.
 *
 * Allows you to add a trait to
 */
trait LinkableControllerTrait {
	use LinkableTrait;

	/**
	 * Helper function to add a link from a controller (like generateUrl only with an extra name argument).
	 *
	 * @param  string $name 		 The link name (self, previous, next, etc..)
	 * @param  string $route         The name of the route
	 * @param  mixed  $parameters    An array of parameters
	 * @param  int    $referenceType The type of reference to be generated (one of the UrlGeneratorInterface constants)
	 *
	 * @return Instance of the class where this trait is used.
	 */
	protected function link(string $name, string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		if (!is_a($this, Controller::class)) {
			throw new LogicException(sprintf("LinkableControllerTrait can only be used in a '%s' or inherited class", Controller::class));
		}

		$link = Link::create($name, $this->generateUrl($route, $parameters, $referenceType));
		$this->addLink($link);

		return $this;
	}
}
