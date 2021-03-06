<?php

namespace Markup\ContentfulBundle\DataCollector;

use Markup\Contentful\Contentful;
use Markup\Contentful\Log\LinkResolveCounterInterface;
use Markup\Contentful\Log\LoggerInterface;
use Markup\Contentful\Log\LogInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ContentfulDataCollector extends DataCollector
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LinkResolveCounterInterface
     */
    private $linkResolveCounter;

    public function __construct(LoggerInterface $logger, LinkResolveCounterInterface $linkResolveCounter)
    {
        $this->logger = $logger;
        $this->linkResolveCounter = $linkResolveCounter;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'logs' => $this->fetchLogs(),
            'linkResolveCount' => count($this->linkResolveCounter),
        ];
    }

    public function reset()
    {
        $this->data = [];
    }

    /**
     * @return LogInterface[]
     */
    public function getLogs()
    {
        return $this->data['logs'];
    }

    public function getQueryCount(): int
    {
        return count($this->data['logs']);
    }

    public function getSerialTimeInSeconds(): float
    {
        $time = 0.0;
        foreach ($this->data['logs'] as $log) {
            /**
             * @var LogInterface $log
             */
            $time += $log->getDurationInSeconds();
        }

        return $time;
    }

    public function getParallelTimeInSeconds(): float
    {
        $intervals = array_filter(
            array_map(
                function (LogInterface $log) {
                    return [$log->getStartTime(), $log->getStopTime()];
                },
                $this->data['logs']
            ),
            function (array $interval) {
                return $interval[0] !== null && $interval[1] !== null;
            }
        );
        $groups = [];
        while (count($intervals) > 0) {
            [$intervals, $startTime, $stopTime] = $this->filterIntervalsIntoGroup($intervals);
            $groups[] = [$startTime, $stopTime];
        }

        return array_reduce(
            $groups,
            function ($carry, array $group) {
                return $carry + $this->convertDateIntervalToSeconds($group[1]->diff($group[0], true));
            },
            0.0
        );
    }

    public function getUsingPreviewApi(): bool
    {
        foreach ($this->data['logs'] as $log) {
            /**
             * @var LogInterface $log
             */
            if ($log->getApi() === Contentful::PREVIEW_API) {
                return true;
            }
            if ($log->getApi() === Contentful::CONTENT_DELIVERY_API) {
                return false;
            }
        }

        return false;
    }

    public function getLinkResolves(): int
    {
        return $this->data['linkResolveCount'] ?? 0;
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName(): string
    {
        return 'contentful';
    }

    /**
     * @return LogInterface[]
     */
    private function fetchLogs()
    {
        return $this->logger->getLogs();
    }

    private function convertDateIntervalToSeconds(\DateInterval $interval): float
    {
        //NB. this implementation only works for durations under one hour (which seems acceptable in this use-case!)
        //it's a slightly shonky implementation because PHP DateIntervals, but should be sufficiently accurate here
        [$minutes, $seconds] = explode(' ', $interval->format('%i %s.%F'));

        return (intval($minutes) * 60) + floatval($seconds);
    }

    private function filterIntervalsIntoGroup(array $intervals)
    {
        $remainder = [];
        $startTime = null;
        $stopTime = null;
        foreach ($intervals as $interval) {
            if (null === $startTime) {
                $startTime = $interval[0];
            }
            if (null === $stopTime) {
                $stopTime = $interval[1];
            }
            if ($interval[0] > $stopTime || $interval[1] < $startTime) {
                $remainder[] = $interval;
                continue;
            }
            if ($interval[0] < $startTime) {
                $startTime = $interval[0];
            }
            if ($interval[1] > $stopTime) {
                $stopTime = $interval[1];
            }
        }

        return [$remainder, $startTime, $stopTime];
    }
}
