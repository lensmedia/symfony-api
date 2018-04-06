<?php

namespace Lens\Bundle\ApiBundle\DependencyInjection;

use Lens\Bundle\ApiBundle\Utils\Api;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LensApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // Set constructor arguments for our Api service class.
        $api = $container
            ->getDefinition(Api::class)
            ->replaceArgument(0, $config)
            ->replaceArgument(1, new Reference($config['serializer']));
    }
}
