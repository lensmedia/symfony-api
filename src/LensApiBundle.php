<?php

namespace Lens\Bundle\ApiBundle;

use Lens\Bundle\ApiBundle\EventListener\ErrorListener;
use Lens\Bundle\ApiBundle\Serializer\CircularReferenceHandler;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class LensApiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $dc = &$config['serializer']['default_context'];
        if (!empty($dc['circular_reference_handler'])) {
            $dc['circular_reference_handler'] = new Reference($dc['circular_reference_handler']);
        }

        $builder
            ->getDefinition(Api::class)
            ->setArgument('$serializer', new Reference($config['serializer']['id']))
            ->setArgument('$options', $config);

        $builder
            ->getDefinition(ContextBuilder::class)
            ->setArgument('$defaultContext', $config['serializer']['default_context']);

        $logger = $config['logger']
            ? new Reference($config['logger'])
            : null;

        $builder->getDefinition(ErrorListener::class)
            ->setArgument('$logger', $logger)
            ->setArgument('$excludedErrors', $config['excluded_errors']);

        $builder->getDefinition(CircularReferenceHandler::class)
            ->setArgument('$logger', $logger);
    }
}
