<?php

namespace Markup\ContentfulBundle\Tests\DataCollector;

use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\ContentfulBundle\DataCollector\ContentfulDataCollector;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class ContentfulDataCollectorTest extends MockeryTestCase
{
    /**
     * @var LoggerInterface|m\MockInterface
     */
    private $logger;

    /**
     * @var ContentfulDataCollector
     */
    private $collector;

    protected function setUp()
    {
        $this->logger = m::mock(LoggerInterface::class);
        $this->collector = new ContentfulDataCollector($this->logger);
    }

    public function testIsDataCollector()
    {
        $this->assertInstanceOf(DataCollectorInterface::class, $this->collector);
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

    public function testSerialTimeInSeconds()
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
        $this->assertEquals(0.5, $this->collector->getSerialTimeInSeconds());
    }

    private function doCollect($collector = null)
    {
        $collector = $collector ?: $this->collector;
        $collector->collect(
            m::mock(Request::class),
            m::mock(Response::class)
        );
    }

    private function getMockLog()
    {
        return m::mock(LogInterface::class);
    }
}
