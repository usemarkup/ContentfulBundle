<?php

namespace Markup\ContentfulBundle\Twig;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\Contentful;
use Markup\Contentful\EntryInterface;
use Markup\Contentful\Exception\ResourceUnavailableException;

class ContentfulExtension extends \Twig_Extension
{
    /**
     * @var Contentful
     */
    private $contentful;

    /**
     * @param Contentful $contentful
     */
    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getTests()
    {
        return [
            new \Twig_SimpleTest('contentful_entry', function ($entry) {
                return $entry instanceof EntryInterface;
            }),
            new \Twig_SimpleTest('contentful_asset', function ($entry) {
                return $entry instanceof AssetInterface;
            })
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contentful_entry', function ($entryId, $spaceName = null, $options = []) {
                try {
                    $entry = $this->contentful->getEntry($entryId, $spaceName, $options);
                } catch (ResourceUnavailableException $e) {
                    return null;
                }

                return $entry;
            }),
            new \Twig_SimpleFunction('contentful_asset', function ($assetId, $spaceName = null, $options = []) {
                try {
                    $asset = $this->contentful->getAsset($assetId, $spaceName, $options);
                } catch (ResourceUnavailableException $e) {
                    return null;
                }

                return $asset;
            }),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'contentful';
    }
}
