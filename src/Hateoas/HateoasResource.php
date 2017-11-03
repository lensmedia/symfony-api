<?php

namespace Lens\Bundle\ApiBundle\Hateoas;

use Lens\Bundle\ApiBundle\HttpFoundation\Resource;

/**
 * *WIP* Represents a resource, a HATEOAS resource can have also have links.
 */
class HateoasResource extends Resource implements LinkableInterface {
	use LinkableTrait;
}
