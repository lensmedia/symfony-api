<?php

declare(strict_types=1);

use Lens\Bundle\ApiBundle\EventListener\ErrorListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Serializer\SerializerInterface;

return static function (DefinitionConfigurator $definition): void {
    static $defaults = [
        'accept' => 'application/json',
        'serializer' => [
            'id' => SerializerInterface::class,
            'default_context' => [],
        ],
        'logger' => LoggerInterface::class,
    ];

    $rootNode = $definition->rootNode();

    $rootNode
        ->children()
            ->scalarNode('accept')->defaultValue($defaults['accept'])->end()
            ->arrayNode('headers')
                ->normalizeKeys(false)
                ->treatNullLike([])
                ->scalarPrototype()->end()
            ->end()
            ->scalarNode('logger')
                ->defaultValue($defaults['logger'])
            ->end()
        ->end();

    // Serializer node
    $rootNode
        ->children()
            ->arrayNode('serializer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('id')
                        ->defaultValue($defaults['serializer']['id'])
                    ->end()
                    ->arrayNode('default_context')
                        ->scalarPrototype()->end()
                        ->defaultValue($defaults['serializer']['default_context'])
                    ->end()
                ->end()
            ->end()
        ->end();

    // Formats node
    $rootNode
        ->children()
            ->arrayNode('formats')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return ['mime-types' => $v];
                        })
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ->end();

    // EntryPoints node
    $rootNode
        ->children()
            ->arrayNode('entry_points')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('path')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return [$v];
                            })
                        ->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('host')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return [$v];
                            })
                        ->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('headers')
                        ->normalizeKeys(false)
                        ->treatNullLike([])
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

    // Excluded errors node
    $rootNode
        ->children()
            ->arrayNode('excluded_errors')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode(ErrorListener::IGNORE_LISTENER)
                        ->defaultValue([])
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode(ErrorListener::IGNORE_LOGGER)
                        ->defaultValue([])
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
