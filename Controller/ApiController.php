<?php

namespace Lens\Bundle\ApiBundle\Controller;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiController extends Controller {
	protected function getManager() {
		return $this
			->getDoctrine()
			->getManager();
	}

	protected function getRepository(string $class) {
		return $this
			->getManager()
			->getRepository($class);
	}

	public static function catchAllAction() {
		return ApiResponse::create(404);
	}
}
