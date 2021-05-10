<?php

namespace Lens\Bundle\ApiBundle\DependencyInjection;

use Lens\Bundle\ApiBundle\Api;
use Lens\Bundle\ApiBundle\ContextBuilder;
use Lens\Bundle\ApiBundle\EventListener\ErrorListener;
use Lens\Bundle\ApiBundle\Serializer\CircularReferenceHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LensApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        // If we have a crh service in our default context options create a reference to use.
        $dc = &$config['serializer']['default_context'];
        if (!empty($dc['circular_reference_handler'])) {
            $dc['circular_reference_handler'] = new Reference($dc['circular_reference_handler']);
        }

        $container
            ->getDefinition(Api::class)
            ->replaceArgument(0, new Reference($config['serializer']['id']))
            ->replaceArgument(3, $config);

        $container
            ->getDefinition(ContextBuilder::class)
            ->replaceArgument(2, $config['serializer']['default_context']);

        $logger = $config['logger']
            ? new Reference($config['logger'])
            : null;

        $container->getDefinition(ErrorListener::class)
            ->replaceArgument(1, $logger)
            ->replaceArgument(2, $config['excluded_errors']);

        $container->getDefinition(CircularReferenceHandler::class)
            ->replaceArgument(0, $logger);
    }
}
