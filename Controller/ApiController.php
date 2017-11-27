<?php

namespace Lens\Bundle\ApiBundle\Controller;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller {
	public function getManager() {
		return $this
			->getDoctrine()
			->getManager();
	}

	public function getRepository(string $class) {
		return $this
			->getManager()
			->getRepository($class);
	}

	public static function catchAllAction() {
		return ApiResponse::create(404);
	}
}
