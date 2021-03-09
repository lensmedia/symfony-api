<?php

namespace Lens\Bundle\ApiBundle\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Context
{
    public function __construct(public string $context)
    {
    }
}
