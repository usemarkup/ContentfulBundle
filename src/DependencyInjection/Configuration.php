<?php

namespace Markup\ContentfulBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
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
        $rootNode = $treeBuilder->root('markup_contentful');

        $rootNode
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->arrayNode('spaces')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
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
                ->end()
                ->booleanNode('dynamic_entries')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
