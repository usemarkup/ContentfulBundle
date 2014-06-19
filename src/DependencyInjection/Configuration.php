<?php

namespace Markup\ContentfulBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('elnur_blowfish_password_encoder');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('spaces')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->scalarNode('key')
                            ->isRequired()
                        ->end()
                        ->scalarNode('access_token')
                            ->isRequired()
                        ->end()
                        ->scalarNode('api_domain')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
