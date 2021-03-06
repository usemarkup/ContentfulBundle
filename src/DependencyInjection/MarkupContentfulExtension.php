<?php

namespace Markup\ContentfulBundle\DependencyInjection;

use GuzzleHttp\HandlerStack;
use Leadz\GuzzleHttp\Stopwatch\StopwatchMiddleware;
use Markup\Contentful\Contentful;
use Markup\Contentful\Log\LinkResolveCounter;
use Markup\ContentfulBundle\Export\ConfigurationEnvelope;
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
     * @param array            $configs   An array of configuration values
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
        $shouldForcePreviewMode = $config['force_preview_mode'];
        foreach ($spacesConfig as $spaceName => $spaceData) {
            if ($shouldForcePreviewMode) {
                $spaceData['preview_mode'] = true;
            }
            if (isset($spaceData['cache']) && $spaceData['cache']) {
                $spaceData['cache'] = new Reference($spaceData['cache']);
            }
            if (isset($spaceData['fallback_cache']) && $spaceData['fallback_cache']) {
                $spaceData['fallback_cache'] = new Reference($spaceData['fallback_cache']);
            }
            if (isset($spaceData['asset_decorator']) && $spaceData['asset_decorator']) {
                $spaceData['asset_decorator'] = new Reference($spaceData['asset_decorator']);
            }
            $spaceData['access_token'] = (!$spaceData['preview_mode']) ? $spaceData['cda_access_token'] : $spaceData['preview_access_token'];
            if (isset($spaceData['resource_envelope']) && $spaceData['resource_envelope']) {
                $spaceData['resource_envelope'] = new Reference($spaceData['resource_envelope']);
            }
            $processedConfig[$spaceName] = $spaceData;
        }
        //by default, we cache fail responses in production, but don't otherwise
        $isProduction = $container->getParameter('kernel.environment') === 'prod';
        $cacheFailResponses = (isset($config['cache_fail_responses'])) ? (bool) $config['cache_fail_responses'] : $isProduction;

        $usingStopwatchHandler = false;
        if ($container->getParameter('kernel.debug')) {
            $usingStopwatchHandler = true;
            $container->setDefinition(
                'markup_contentful.stopwatch_middleware',
                (new Definition(StopwatchMiddleware::class))
                    ->setArguments([new Reference('debug.stopwatch')])
                    ->setPublic(false)
            );
            $container->setDefinition(
                'markup_contentful.stopwatch_handler',
                (new Definition(HandlerStack::class))
                    ->setFactory([HandlerStack::class, 'create'])
                    ->addMethodCall('push', [new Reference('markup_contentful.stopwatch_middleware')])
                    ->setPublic(false)
            );
        }

        $contentful = new Definition(
            Contentful::class,
            [
                $processedConfig,
                array_merge(
                    [
                        'dynamic_entries' => $config['dynamic_entries'],
                        'include_level' => $config['include_level'],
                        'guzzle_connection_timeout' => $config['connection_timeout'],
                        'guzzle_timeout' => $config['connection_timeout'],
                        'cache_fail_responses' => $cacheFailResponses,
                    ],
                    ($usingStopwatchHandler)
                        ? [
                            'guzzle_handler' => new Reference('markup_contentful.stopwatch_handler')
                        ]
                        : [],
                    ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug'))
                        ? [
                            'logger' => new Reference('markup_contentful.stopwatch_logger'),
                            'link_resolve_counter' => new Reference(LinkResolveCounter::class),
                        ]
                        : []
                )
            ]
        );
        $contentful->setPublic(true);
        $container->setDefinition(Contentful::class, $contentful);
        $container->setAlias('markup_contentful', Contentful::class);

        if ($config['expose_space_configurations']) {
            $this->loadExposedSpaceConfigurations($processedConfig, $container);
        }
    }

    private function loadExposedSpaceConfigurations(array $processedConfig, ContainerBuilder $container)
    {
        $envelope = new Definition(ConfigurationEnvelope::class, [$processedConfig]);
        $container->setDefinition(ConfigurationEnvelope::class, $envelope);
    }
}
