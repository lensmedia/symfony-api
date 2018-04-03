<?php

namespace Lens\Bundle\ApiBundle\Controller;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatchAllController extends Controller
{
    /**
     * Can be configured to catch 404 under a specific directory.
     * This is used for routes not bound to a controller to test if it is an API request.
     *
     * @return ApiResponse generic API 404 response
     */
    public static function catchAll(Request $request, string $path)
    {
        if ('OPTIONS' === $request->getMethod()) {
            return new Response();
        }

        throw $this->createNotFoundException(sprintf("No resource found for path '%s' using method '%s'.", $path, $request->getMethod()));
    }
}
