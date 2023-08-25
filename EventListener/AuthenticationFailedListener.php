<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Api;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

/**
 * Bridge between the security component authentication failed handlers
 * and our api output (basically just copies headers resolved in the handlers).
 */
#[AsEventListener]
final class AuthenticationFailedListener
{
    public function __construct(
        private readonly Api $api,
    ) {
    }

    public function __invoke(LoginFailureEvent $loginFailureEvent): void
    {
        $request = $loginFailureEvent->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $exception = $loginFailureEvent->getException();
        $response = $loginFailureEvent->getResponse();

        throw new AccessDeniedHttpException(
            $exception->getMessage(),
            $exception,
            headers: $response?->headers->all() ?? [],
        );
    }
}
