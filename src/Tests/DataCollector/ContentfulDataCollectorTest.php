<?php

namespace Markup\ContentfulBundle\Tests\DataCollector;

use Markup\ContentfulBundle\DataCollector\ContentfulDataCollector;
use Mockery as m;

class ContentfulDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->logger = m::mock('Markup\Contentful\Log\LoggerInterface');
        $this->collector = new ContentfulDataCollector($this->logger);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testIsDataCollector()
    {
        $this->assertInstanceOf('Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface', $this->collector);
    }

    public function testGetLogs()
    {
        $logs = [$this->getMockLog()];
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn($logs);
        $this->doCollect();
        $this->assertEquals($logs, $this->collector->getLogs());
    }

    public function testQueryCount()
    {
        $log = $this->getMockLog();
        $logs = [$log, $log, $log, $log];
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn($logs);
        $this->doCollect();
        $this->assertEquals(4, $this->collector->getQueryCount());
    }

    public function testCacheHitCount()
    {
        $hitLog = $this->getMockLog();
        $hitLog
            ->shouldReceive('isCacheHit')
            ->andReturn(true);
        $missLog = $this->getMockLog();
        $missLog
            ->shouldReceive('isCacheHit')
            ->andReturn(false);
        $logs = [$hitLog, $hitLog, $missLog, $missLog, $hitLog];
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn($logs);
        $this->doCollect();
        $this->assertEquals(3, $this->collector->getCacheHitCount());
    }

    public function testTimeInSeconds()
    {
        $log1 = $this->getMockLog();
        $log1
            ->shouldReceive('getDurationInSeconds')
            ->andReturn(0.2);
        $log2 = $this->getMockLog();
        $log2
            ->shouldReceive('getDurationInSeconds')
            ->andReturn(0.3);
        $logs = [$log1, $log2];
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn($logs);
        $this->doCollect();
        $this->assertEquals(0.5, $this->collector->getTimeInSeconds());
    }

    private function doCollect($collector = null)
    {
        $collector = $collector ?: $this->collector;
        $collector->collect(
            m::mock('Symfony\Component\HttpFoundation\Request'),
            m::mock('Symfony\Component\HttpFoundation\Response')
        );
    }

    private function getMockLog()
    {
        return m::mock('Markup\Contentful\Log\LogInterface');
    }
}
