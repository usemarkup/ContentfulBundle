<?php

namespace Markup\ContentfulBundle\Export;

/**
 * A configuration object exposing details for a specific space so the configuration could be used with other code,
 * such as Contentful's own SDK.
 */
class SpaceConfiguration
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getSpaceKey(): string
    {
        return $this->data['key'] ?? '';
    }

    public function getAccessToken(): string
    {
        return $this->data['access_token'] ?? '';
    }

    public function getCdaAccessToken(): ?string
    {
        return $this->data['cda_access_token'] ?? null;
    }

    public function getPreviewAccessToken(): ?string
    {
        return $this->data['preview_access_token'] ?? null;
    }
}
