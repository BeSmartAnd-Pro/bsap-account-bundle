<?php

declare(strict_types=1);

namespace BeSmartAndPro\BsapAccountBundle;

use BeSmartAndPro\BsapAccountBundle\Auth\AuthService;
use BeSmartAndPro\BsapAccountBundle\Client\InvoiceClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class BsapAccountBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('mode')->isRequired()->end()
            ->scalarNode('username')->isRequired()->end()
            ->scalarNode('password')->isRequired()->end()
            ->scalarNode('alternativeHost')->end()
            ->end()
        ;
    }
    
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        
        $container
            ->services()
            ->get(AuthService::class)
            ->args([
                '$mode'            => $config['mode'],
                '$username'        => $config['username'],
                '$password'        => $config['password'],
                '$alternativeHost' => $config['alternativeHost'] ?? null,
            ]);
        
        $container
            ->services()
            ->get(InvoiceClient::class)
            ->args([
                '$mode'            => $config['mode'],
                '$alternativeHost' => $config['alternativeHost'] ?? null,
            ]);
    }
}
