<?php
namespace Lens\Bundle\ApiBundle\Controller;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller {
	/**
	 * Helper function for our doctrine manager.
	 *
	 * @return EntityManager
	 */
	protected function getManager() {
		return $this
			->getDoctrine()
			->getManager();
	}

	/**
	 * Helper function for a specific repository.
	 *
	 * @param  string       $class
	 * @return Repository
	 */
	protected function getRepository(string $class) {
		return $this
			->getManager()
			->getRepository($class);
	}

	/**
	 * Can be configured to catch 404 under a specific directory.
	 * This is used for routes not bound to a controller to test if it is an API request.
	 *
	 * @return ApiResponse Generic API 404 response.
	 */
	public static function catchAllAction() {
		return ApiResponse::create(404);
	}
}
