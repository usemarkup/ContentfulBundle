<?php

namespace Markup\ContentfulBundle\Log;

use Markup\Contentful\Log\TimerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A timer object that uses a Symfony stopwatch.
 */
class StopwatchTimer implements TimerInterface
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
     * @var string
     */
    private $stopwatchCategory;

    /**
     * @var string
     */
    private $stopwatchSection;

    /**
     * @var bool
     */
    private $wasStarted;

    /**
     * @var bool
     */
    private $wasStopped;

    /**
     * @var float
     */
    private $durationAtStop;

    /**
     * @param Stopwatch $stopwatch
     * @param string    $uniqueId
     */
    public function __construct(Stopwatch $stopwatch, $uniqueId, $stopwatchCategory = null, $stopwatchSection = null)
    {
        $this->stopwatch = $stopwatch;
        $this->uniqueId = $uniqueId;
        $this->stopwatchCategory = $stopwatchCategory;
        $this->stopwatchSection = $stopwatchSection;
        $this->wasStarted = false;
        $this->wasStopped = false;
    }

    public function start()
    {
        if ($this->isStarted()) {
            return;
        }
        if ($this->stopwatchSection) {
            $this->stopwatch->openSection();
        }
        $this->stopwatch->start($this->uniqueId, $this->stopwatchCategory);
        $this->wasStarted = true;
    }

    public function stop()
    {
        if ($this->isStopped()) {
            return;
        }
        if ($this->stopwatchSection) {
            $this->stopwatch->stopSection($this->stopwatchSection);
        }
        $event = $this->stopwatch->stop($this->uniqueId);
        $this->durationAtStop = floatval($event->getDuration()/1000);
        $this->wasStopped = true;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->wasStarted;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->wasStopped;
    }

    /**
     * @return float
     */
    public function getDurationInSeconds()
    {
        if (!$this->isStarted()) {
            return null;
        }
        if (!$this->isStopped()) {
            return (method_exists($this->stopwatch, 'getEvent')) ? floatval($this->stopwatch->getEvent($this->uniqueId)->getDuration()/1000) : null;
        }

        return $this->durationAtStop;
    }
}
