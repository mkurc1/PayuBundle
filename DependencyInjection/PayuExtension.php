<?php

namespace PayuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class PayuExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('payu.class.request', $config['class']['request']);
        $container->setParameter('payu.environment', $config['environment']);
        $container->setParameter('payu.cipher', $config['cipher']);
        $container->setParameter('payu.redirect', $config['redirect']);
        $container->setParameter('payu.pos_id', $config['pos_id']);
        $container->setParameter('payu.signature_key', $config['signature_key']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}