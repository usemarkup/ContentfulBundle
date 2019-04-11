<?php

namespace Markup\ContentfulBundle\Log;

use Markup\Contentful\Log\Log;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Markup\Contentful\Log\TimerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchLogger implements LoggerInterface
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var string|null
     */
    private $stopwatchCategory;

    /**
     * @var string|null
     */
    private $stopwatchSection;

    /**
     * @var array<LogInterface>
     */
    private $logs;

    /**
     * @var int
     */
    private $timerCount;

    /**
     * @param Stopwatch $stopwatch
     * @param string $stopwatchCategory
     * @param string $stopwatchSection
     */
    public function __construct(Stopwatch $stopwatch = null, $stopwatchCategory = null, $stopwatchSection = null)
    {
        $this->stopwatch = $stopwatch ?: new Stopwatch();
        $this->stopwatchCategory = $stopwatchCategory;
        $this->stopwatchSection = $stopwatchSection;
        $this->logs = [];
        $this->timerCount = 1;
    }

    /**
     * Gets a new timer that has already been started.
     *
     * @return TimerInterface
     */
    public function getStartedTimer()
    {
        $timer = new StopwatchTimer($this->stopwatch, sprintf('Contentful fetch #%u', $this->timerCount), $this->stopwatchCategory, $this->stopwatchSection);
        $timer->start();
        $this->timerCount++;

        return $timer;
    }

    /**
     * Logs a lookup.
     *
     * @param string         $description A description of what this lookup was, including pertinent information such as URLs and cache keys.
     * @param bool           $isCacheHit
     * @param TimerInterface $timer       A timer. If it is started but not stopped, it will be stopped and a reading taken. If
     * @param string         $resourceType
     * @param string         $api
     */
    public function log($description, $isCacheHit, TimerInterface $timer = null, $resourceType, $api)
    {
        if (!$timer) {
            return;
        }
        if ($timer->isStarted()) {
            $timer->stop();//will have no effect if already stopped
            $duration = $timer->getDurationInSeconds();
        } else {
            $duration = null;
        }
        $this->logs[] = new Log(
            $description,
            $duration,
            $timer->getStartTime(),
            $timer->getStopTime(),
            $isCacheHit,
            $resourceType,
            $api
        );
    }

    /**
     * Gets the collected logs.
     *
     * @return LogInterface[]
     */
    public function getLogs()
    {
        return $this->logs;
    }
}
