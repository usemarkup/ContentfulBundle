<?php
declare(strict_types=1);

namespace Markup\ContentfulBundle\Tests\Export;

use Markup\ContentfulBundle\Export\ConfigurationEnvelope;
use Markup\ContentfulBundle\Export\SpaceConfiguration;
use PHPUnit\Framework\TestCase;

class ConfigurationEnvelopeTest extends TestCase
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $space;

    /**
     * @var ConfigurationEnvelope
     */
    private $envelope;

    protected function setUp()
    {
        $this->key = 'kewjlfhdskljfh';
        $config = [
            'key' => $this->key,
            'access_token' => 'lskjfsdkljhf',
        ];
        $this->space = 'i_am_a_space';
        $data = [
            $this->space => $config,
        ];
        $this->envelope = new ConfigurationEnvelope($data);
    }

    public function testAccessConfiguration()
    {
        $this->assertEquals($this->key, $this->envelope->findConfigForSpace($this->space)->getSpaceKey());
    }

    public function testGetAllSpaceConfigs()
    {
        $configs = $this->envelope->getAllSpaceConfigs();
        $this->assertEquals([$this->space], array_keys($configs));
        $this->assertContainsOnlyInstancesOf(SpaceConfiguration::class, $configs);
    }
}
