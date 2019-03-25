<?php
declare(strict_types=1);

namespace Markup\ContentfulBundle\Tests\Export;

use Markup\ContentfulBundle\Export\SpaceConfiguration;
use PHPUnit\Framework\TestCase;

class SpaceConfigurationTest extends TestCase
{
    public function testGetters()
    {
        $key = 'ldsjkghdklsgjhdfklj';
        $accessToken = 'kljhsdkljhsdfjkh';
        $data = [
            'key' => $key,
            'access_token' => $accessToken,
            'cda_access_token' => $accessToken,
        ];
        $config = new SpaceConfiguration($data);
        $this->assertEquals($key, $config->getSpaceKey());
        $this->assertEquals($accessToken, $config->getAccessToken());
        $this->assertEquals($accessToken, $config->getCdaAccessToken());
        $this->assertNull($config->getPreviewAccessToken());
    }
}
