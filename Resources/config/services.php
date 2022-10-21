<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lens\Bundle\ApiBundle\EventListener\ContentNegotiationListener;
use Lens\Bundle\ApiBundle\EventListener\ErrorListener;
use Lens\Bundle\ApiBundle\EventListener\OptionsRequestListener;
use Lens\Bundle\ApiBundle\EventListener\ViewListener;
use Lens\Bundle\ApiBundle\Serializer\CircularReferenceHandler;
use Lens\Bundle\ApiBundle\Serializer\Normalizer\ErrorNormalizer;
use Lens\Bundle\ApiBundle\Api;
use Lens\Bundle\ApiBundle\ContextBuilder;
use Lens\Bundle\ApiBundle\ContextBuilderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Api::class)
            ->args([
                service(SerializerInterface::class),
                service(RequestStack::class),
                service(ContextBuilder::class),
                [],
            ])

        ->set(ContextBuilder::class)
            ->args([
                service(RequestStack::class),
                service(TokenStorageInterface::class),
                [],
            ])

        ->alias(ContextBuilderInterface::class, ContextBuilder::class)

        ->set(CircularReferenceHandler::class)
            ->args([service(LoggerInterface::class)])
            ->public()

        ->set(ContentNegotiationListener::class)
            ->args([service(Api::class)])
            ->tag('kernel.event_listener', [
                'event' => KernelEvents::REQUEST,
                'priority' => 9989
            ])

        ->set(OptionsRequestListener::class)
            ->args([service(Api::class)])
            ->tag('kernel.event_listener', [
                'event' => KernelEvents::REQUEST,
                'priority' => 9999
            ])

        ->set(ViewListener::class)
            ->args([service(Api::class)])
            ->tag('kernel.event_listener', [
                'event' => KernelEvents::VIEW,
                'priority' => 9999
            ])

        ->set(ErrorListener::class)
            ->args([
                service(Api::class),
                service(LoggerInterface::class),
                service(RequestStack::class),
                [],
            ])
            ->tag('kernel.event_listener', [
                'event' => KernelEvents::EXCEPTION,
                'priority' => 9999
            ])

        ->set(ErrorNormalizer::class)
            ->tag('serializer.normalizer')
    ;
};
