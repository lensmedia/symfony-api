<?php

namespace Lens\Bundle\ApiBundle;

use Lens\Bundle\ApiBundle\DependencyInjection\LensApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LensApiBundle extends Bundle {
	public function getContainerExtension() {
		return new LensApiExtension();
	}
}
