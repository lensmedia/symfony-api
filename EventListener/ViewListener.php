<?php

namespace Lens\Bundle\ApiBundle\EventListener;

use Lens\Bundle\ApiBundle\Utils\Api;
use Lens\Bundle\ApiBundle\Utils\ContextBuilderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Our view listener intercepts any return from controllers that are not instances/inherited of a Response class.
 *
 * In here we convert any data to a response for it to be serialized and returned.
 */
final class ViewListener
{
    private $api;
    private $contextBuilder;
    private $stopwatch;
    private $logger;

    public function __construct(Api $api = null, ContextBuilderInterface $contextBuilder, LoggerInterface $logger)
    {
        $this->api = $api;
        $this->contextBuilder = $contextBuilder;
        $this->logger = $logger;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $stopwatch = new Stopwatch(true);
        $stopwatch->openSection();

        ///////////////////////////////////
        $stopwatch->start('check_request');
        ///////////////////////////////////
        $request = $event->getRequest();
        if (!$this->api->isApiRequest($request)) {
            return;
        }

        $stopwatch->stop('check_request');

        $headers = $this->api->getResponseHeaders($request);
        $controllerResult = $event->getControllerResult();
        if (null === $controllerResult) {
            $response = new Response(null, Response::HTTP_NO_CONTENT, $headers);

            return $event->setResponse($response);
        }

        /////////////////////////////////////
        $stopwatch->start('context_builder');
        /////////////////////////////////////
        $context = array_merge_recursive(
            $this->api->serializerDefaultContext(),
            $this->contextBuilder->getContext()
        );
        $stopwatch->stop('context_builder');

        ///////////////////////////////////
        $stopwatch->start('serialization');
        ///////////////////////////////////
        $contentType = $this->api->getContentTypeMatch($request)->getType();

        $content = $this->api->getSerializer()->serialize(
            $controllerResult,
            $this->api->getFormatForMimeType($contentType),
            $context
        );
        $stopwatch->stop('serialization');

        /////////////////////////////////////
        $stopwatch->start('create_response');
        /////////////////////////////////////
        $response = new Response($content, Response::HTTP_OK, $headers);
        $event->setResponse($response);
        $stopwatch->stop('create_response');

        ///////////////////
        // stopwatch end //
        ///////////////////

        $stopwatch->stopSection('view_listener');
        $events = $stopwatch->getSectionEvents('view_listener');

        $section = array_shift($events);

        $totalDuration = $section->getDuration();
        if ($totalDuration >= 10) {
            $events = implode(', ', array_map(function ($event) use ($events) {
                return sprintf(
                    '%s: %.2F MiB - %d ms',
                    array_search($event, $events),
                    $event->getMemory() / 1024 / 1024,
                    $event->getDuration()
                );
            }, $events));

            $this->logger->warning('Api view listener execution time surpassed 500ms; '.((string) $section).'.', [
                'target' => $event->getRequest()->getRequestUri(),
                'events' => $events,
            ]);
        }
    }
}
