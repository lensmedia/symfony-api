<?php

namespace Lens\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('lens_api');
        $rootNode = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->append($this->addSerializerNode())
                ->scalarNode('accept')->defaultValue('application/json')->end()
                ->append($this->addFormatNode())
                ->append($this->addEntryPointNode())
            ->end();

        return $treeBuilder;
    }

    private function addSerializerNode()
    {
        $treeBuilder = new TreeBuilder('serializer');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('id')->defaultValue('serializer')->end()
                ->arrayNode('default_context')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $node;
    }

    private function addFormatNode()
    {
        $treeBuilder = new TreeBuilder('formats');
        $node = $treeBuilder->getRootNode();

        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($v) {
                        return ['mime-types' => $v];
                    })
                ->end()
                ->scalarPrototype()->end()
            ->end();

        return $node;
    }

    private function addEntryPointNode()
    {
        $treeBuilder = new TreeBuilder('entry_points');
        $node = $treeBuilder->getRootNode();

        $node
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
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
