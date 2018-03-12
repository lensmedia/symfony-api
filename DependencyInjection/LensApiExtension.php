<?php

namespace Lens\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LensApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lens_api.hateoas', $config['hateoas']);
        $container->setParameter('lens_api.exclusive', $config['exclusive']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/Config'));
        $loader->load('config.yml');
    }
}
