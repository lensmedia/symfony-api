<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Controller\ApiController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core API event subscriber things.
 */
abstract class AbstractApiEventSubscriber implements EventSubscriberInterface
{
    /**
     * Checks if the request matches a controller which inherits from ApiController.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected static function isApiRequest(Request $request, Response $response = null): bool
    {
        echo 'controller '.$request->headers->get('host');
        // Strip the method call.
        $requestControllerClass = strstr($request->get('_controller'), ':', true);
        if (false === $requestControllerClass) {
            return false;
        }

        return is_a($requestControllerClass, ApiController::class, true);
    }

    abstract public static function getSubscribedEvents();
}
