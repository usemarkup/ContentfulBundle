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
                                ->defaultNull()
                                ->setDeprecated(
                                    'The "%node%" configuration option is deprecated - you should use "cda_access_token" for the CDA or "preview_access_token" for the Preview API.'
                                )
                            ->end()
                            ->scalarNode('cda_access_token')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('preview_access_token')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('api_domain')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('cache')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('fallback_cache')
                                ->defaultNull()
                            ->end()
                            ->booleanNode('preview_mode')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('asset_decorator')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('resource_envelope')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->integerNode('include_level')
                    ->defaultValue(6)
                ->end()
                ->booleanNode('dynamic_entries')
                    ->defaultTrue()
                ->end()
                ->integerNode('connection_timeout')
                    ->defaultValue(0)
                ->end()
                ->booleanNode('cache_fail_responses')
                ->end()
                ->booleanNode('force_preview_mode')
                    ->defaultFalse()
                ->end()
                ->booleanNode('expose_space_configurations')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
