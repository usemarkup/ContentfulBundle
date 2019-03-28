<?php

namespace Markup\ContentfulBundle\Tests\Log;

use Markup\Contentful\Log\TimerInterface;
use Markup\ContentfulBundle\Log\StopwatchTimer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchTimerTest extends TestCase
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var StopwatchTimer
     */
    private $timer;

    protected function setUp()
    {
        $this->stopwatch = new Stopwatch();
        $this->uniqueId = 'i_am_unique';
        $this->timer = new StopwatchTimer($this->stopwatch, $this->uniqueId);
    }

    public function testIsTimer()
    {
        $this->assertInstanceOf(TimerInterface::class, $this->timer);
    }

    public function testStandardCycle()
    {
        $this->assertFalse($this->timer->isStarted());
        $this->timer->start();
        $this->assertTrue($this->timer->isStarted());
        $this->assertFalse($this->timer->isStopped());
        $runningDuration = $this->timer->getDurationInSeconds();
        $this->assertIsFloat($runningDuration);
        $this->assertLessThan(2, $runningDuration);//seems reasonable to suppose that this will have executed in less than 2s
        $this->timer->stop();
        $this->assertTrue($this->timer->isStopped());
        $finalDuration = $this->timer->getDurationInSeconds();
        $secondFinalDuration = $this->timer->getDurationInSeconds();
        $this->assertSame($finalDuration, $secondFinalDuration);
        $this->assertIsFloat($finalDuration);
        $this->assertGreaterThanOrEqual($runningDuration, $finalDuration);
        $this->assertLessThan(2, $finalDuration);//same applies to final duration
        $this->timer->start();
        $this->timer->stop();
        $this->assertEquals($finalDuration, $this->timer->getDurationInSeconds(), 'the timer cannot be reused');
    }
}
