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
    const DEFAULTS = [
        'serializer' => 'serializer',
        'accept' => 'application/json',

        // access control is a sub section of each entry point
        'access_control' => [
            'allow' => [
                'origin' => ['*'],
                'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'headers' => ['content-type', 'origin', 'authorization', 'accept'],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lens_api');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('serializer')->defaultValue(self::DEFAULTS['serializer'])->end()
                ->scalarNode('accept')->defaultValue(self::DEFAULTS['accept'])->end()
                ->append($this->addFormatNode())
                ->append($this->addEntryPointNode())
            ->end();

        return $treeBuilder;
    }

    private function addFormatNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('formats');

        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($v) { return ['mime-types' => $v]; })
                ->end()
                ->scalarPrototype()->end()
            ->end();

        return $node;
    }

    private function addEntryPointNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('entry_points');

        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('path')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return [$v]; })
                        ->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('host')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return [$v]; })
                        ->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->append($this->addAccessControlNode())
                ->end()
            ->end();

        return $node;
    }

    private function addAccessControlNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('access_control');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('allow')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('origin')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array_map(function ($j) {
                                        return trim($j);
                                    }, preg_split('~\s*,\s*~', $v, -1, PREG_SPLIT_NO_EMPTY));
                                })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue(self::DEFAULTS['access_control']['allow']['origin'])
                        ->end()
                        ->arrayNode('methods')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array_map(function ($j) {
                                        return trim($j);
                                    }, preg_split('~\s*,\s*~', $v, -1, PREG_SPLIT_NO_EMPTY));
                                })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue(self::DEFAULTS['access_control']['allow']['methods'])
                        ->end()
                        ->arrayNode('headers')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return array_map(function ($j) {
                                        return trim($j);
                                    }, preg_split('~\s*,\s*~', $v, -1, PREG_SPLIT_NO_EMPTY));
                                })
                            ->end()
                            ->scalarPrototype()->end()
                            ->defaultValue(self::DEFAULTS['access_control']['allow']['headers'])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
