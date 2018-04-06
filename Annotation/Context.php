<?php

namespace Lens\Bundle\ApiBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Context
{
    public $name;
}
