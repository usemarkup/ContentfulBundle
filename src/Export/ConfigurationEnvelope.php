<?php

namespace Markup\ContentfulBundle\Export;

/**
 * An envelope exposing configurations on a per-space basis.
 */
class ConfigurationEnvelope
{
    /**
     * @var array
     */
    private $configData;

    public function __construct(array $configData)
    {
        $this->configData = $configData;
    }

    public function findConfigForSpace(string $space): SpaceConfiguration
    {
        if (!array_key_exists($space, $this->configData)) {
            throw new \LogicException(sprintf('Could not locate a configuration defined for a space "%s".', $space));
        }

        return new SpaceConfiguration($this->configData[$space]);
    }

    public function getAllSpaceConfigs(): array
    {
        return array_map(
            function (array $data) {
                return new SpaceConfiguration($data);
            },
            $this->configData
        );
    }
}
