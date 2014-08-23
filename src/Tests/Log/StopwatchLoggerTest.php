<?php

namespace Markup\ContentfulBundle\Tests\Log;

use Markup\Contentful\Log\LogInterface;
use Markup\ContentfulBundle\Log\StopwatchLogger;

class StopwatchLoggerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->logger = new StopwatchLogger();
    }

    public function testIsLogger()
    {
        $this->assertInstanceOf('Markup\Contentful\Log\LoggerInterface', $this->logger);
    }

    public function testLogOneItem()
    {
        $initialLogs = $this->logger->getLogs();
        $this->assertCount(0, $initialLogs);
        $timer = $this->logger->getStartedTimer();
        $this->assertInstanceOf('Markup\Contentful\Log\TimerInterface', $timer);
        $this->assertTrue($timer->isStarted());
        $description = 'description goes here';
        $isCacheHit = true;
        $type = LogInterface::TYPE_RESOURCE;
        $resourceType = LogInterface::RESOURCE_ASSET;
        $this->logger->log($description, $isCacheHit, $timer, $type, $resourceType);
        $finalLogs = $this->logger->getLogs();
        $this->assertCount(1, $finalLogs);
        $log = reset($finalLogs);
        $this->assertInstanceOf('Markup\Contentful\Log\LogInterface', $log);
        $this->assertEquals($type, $log->getType());
        $this->assertEquals($description, $log->getDescription());
        $this->assertInternalType('float', $log->getDurationInSeconds());
        $this->assertLessThan(1, $log->getDurationInSeconds());
    }
}
