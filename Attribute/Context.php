<?php

namespace Lens\Bundle\ApiBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Context
{
    public function __construct(
        public readonly string $context,
    ) {
    }
}
