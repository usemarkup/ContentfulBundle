<?php

namespace Markup\ContentfulBundle\Twig;

use Markup\Contentful\AssetInterface;
use Markup\Contentful\EntryInterface;

class ContentfulExtension extends \Twig_Extension
{
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
