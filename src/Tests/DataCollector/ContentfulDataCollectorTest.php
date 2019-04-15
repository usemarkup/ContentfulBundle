<?php

namespace Markup\ContentfulBundle\Tests\DataCollector;

use Markup\Contentful\Log\LinkResolveCounterInterface;
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
     * @var LinkResolveCounterInterface
     */
    private $linkResolveCounter;

    /**
     * @var int
     */
    private $linkResolveCount;

    /**
     * @var ContentfulDataCollector
     */
    private $collector;

    protected function setUp()
    {
        $this->logger = m::mock(LoggerInterface::class);
        $this->linkResolveCount = 42;
        $this->linkResolveCounter = m::mock(LinkResolveCounterInterface::class)
            ->shouldReceive('count')
            ->andReturn($this->linkResolveCount)
            ->getMock();
        $this->collector = new ContentfulDataCollector(
            $this->logger,
            $this->linkResolveCounter
        );
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

    public function testParallelTimeInSeconds()
    {
        $log1 = $this->getMockLog()
            ->shouldReceive('getStartTime')
            ->andReturn(new \DateTimeImmutable('2019-04-15 16:15:15'))
            ->getMock()
            ->shouldReceive('getStopTime')
            ->andReturn(new \DateTimeImmutable('2019-04-15 16:15:20'))
            ->getMock();
        $log2 = $this->getMockLog()
            ->shouldReceive('getStartTime')
            ->andReturn(new \DateTimeImmutable('2019-04-15 16:15:25'))
            ->getMock()
            ->shouldReceive('getStopTime')
            ->andReturn(new \DateTimeImmutable('2019-04-15 16:15:30'))
            ->getMock();
        $logs = [$log1, $log2];
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn($logs);
        $this->doCollect();
        $this->assertEquals(10.0, $this->collector->getParallelTimeInSeconds());
    }

    public function testGetLinkResolves()
    {
        $this->logger
            ->shouldReceive('getLogs')
            ->andReturn([]);
        $this->doCollect();
        $this->assertEquals($this->linkResolveCount, $this->collector->getLinkResolves());
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
