<?php

namespace Markup\ContentfulBundle\Tests\Log;

use Markup\ContentfulBundle\Log\StopwatchTimer;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchTimerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stopwatch = new Stopwatch();
        $this->uniqueId = 'i_am_unique';
        $this->timer = new StopwatchTimer($this->stopwatch, $this->uniqueId);
    }

    public function testIsTimer()
    {
        $this->assertInstanceOf('Markup\Contentful\Log\TimerInterface', $this->timer);
    }

    public function testStandardCycle()
    {
        //if this is < symfony 2.5, this isn't going to be supported, so skip it
        if (version_compare(Kernel::VERSION, '2.5.0', 'lt')) {
            $this->markTestSkipped('Stopwatch timer is not supported for below Symfony 2.5.');
        }
        $this->assertFalse($this->timer->isStarted());
        $this->timer->start();
        $this->assertTrue($this->timer->isStarted());
        $this->assertFalse($this->timer->isStopped());
        $runningDuration = $this->timer->getDurationInSeconds();
        $this->assertInternalType('float', $runningDuration);
        $this->assertLessThan(2, $runningDuration);//seems reasonable to suppose that this will have executed in less than 2s
        $this->timer->stop();
        $this->assertTrue($this->timer->isStopped());
        $finalDuration = $this->timer->getDurationInSeconds();
        $secondFinalDuration = $this->timer->getDurationInSeconds();
        $this->assertSame($finalDuration, $secondFinalDuration);
        $this->assertInternalType('float', $finalDuration);
        $this->assertGreaterThanOrEqual($runningDuration, $finalDuration);
        $this->assertLessThan(2, $finalDuration);//same applies to final duration
        $this->timer->start();
        $this->timer->stop();
        $this->assertEquals($finalDuration, $this->timer->getDurationInSeconds(), 'the timer cannot be reused');
    }
}
