<?php

namespace PayuBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('payu');

        $rootNode
            ->children()
                ->arrayNode('class')->isRequired()
                    ->children()
                        ->scalarNode('request')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode('environment')->defaultValue('secure')->end()
                ->scalarNode('cipher')->defaultValue('TLSv1')->end()
                ->scalarNode('redirect')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('pos_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('signature_key')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}