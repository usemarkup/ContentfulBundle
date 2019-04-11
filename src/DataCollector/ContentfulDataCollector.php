<?php

namespace Markup\ContentfulBundle\DataCollector;

use Markup\Contentful\Contentful;
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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $startTime = null;
        $stopTime = null;
        foreach ($this->data['logs'] as $log) {
            /** @var LogInterface $log */
            if (null === $startTime) {
                $startTime = $log->getStartTime();
            }
            if (null === $stopTime) {
                $stopTime = $log->getStopTime();
            }
            if ($log->getStartTime() < $startTime) {
                $startTime = $log->getStartTime();
            }
            if ($log->getStopTime() > $stopTime) {
                $stopTime = $log->getStopTime();
            }
        }
        if (null === $startTime || null === $stopTime) {
            return 0.0;
        }

        return $this->convertDateIntervalToSeconds($stopTime->diff($startTime, true));
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
}
