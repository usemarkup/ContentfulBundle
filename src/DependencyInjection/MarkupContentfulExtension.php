<?php

namespace Markup\ContentfulBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MarkupContentfulExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['enabled']) {
            $this->loadContentful($config, $container);
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadContentful(array $config, ContainerBuilder $container)
    {
        $spacesConfig = $config['spaces'];
        $processedConfig = [];
        foreach ($spacesConfig as $spaceName => $spaceData) {
            if (isset($spaceData['cache']) && $spaceData['cache']) {
                $spaceData['cache'] = new Reference($spaceData['cache']);
            }
            if (isset($spaceData['fallback_cache']) && $spaceData['fallback_cache']) {
                $spaceData['fallback_cache'] = new Reference($spaceData['fallback_cache']);
            }
            if (isset($spaceData['asset_decorator']) && $spaceData['asset_decorator']) {
                $spaceData['asset_decorator'] = new Reference($spaceData['asset_decorator']);
            }
            $processedConfig[$spaceName] = $spaceData;
        }

        $contentful = new Definition(
            'Markup\Contentful\Contentful',
            [
                $processedConfig,
                [
                    'dynamic_entries' => $config['dynamic_entries'],
                    'include_level' => $config['include_level'],
                    'logger' => new Reference('markup_contentful.stopwatch_logger'),
                    'guzzle_timeout' => $config['connection_timeout'],
                ],
            ]
        );
        $container->setDefinition('markup_contentful', $contentful);
    }
}
