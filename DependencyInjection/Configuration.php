<?php

namespace Lens\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Serializer\SerializerInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULTS = [
        'accept' => 'application/json',
        'serializer' => [
            'id' => SerializerInterface::class,
            'default_context' => [],
        ]
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lens_api');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('accept')->defaultValue(self::DEFAULTS['accept'])->end()
                ->arrayNode('headers')
                    ->normalizeKeys(false)
                    ->treatNullLike([])
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        $this->addSerializerNode($rootNode);
        $this->addFormatNode($rootNode);
        $this->addEntryPointNode($rootNode);

        return $treeBuilder;
    }

    private function addSerializerNode(ArrayNodeDefinition $rootNode)
    {
        return $rootNode
            ->children()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('id')
                            ->defaultValue(self::DEFAULTS['serializer']['id'])
                        ->end()
                        ->arrayNode('default_context')
                            ->scalarPrototype()->end()
                            ->defaultValue(self::DEFAULTS['serializer']['default_context'])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addFormatNode(ArrayNodeDefinition $rootNode)
    {
        return $rootNode
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
    }

    private function addEntryPointNode(ArrayNodeDefinition $rootNode)
    {
        return $rootNode
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
    }
}
