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

    /**
     * @return LogInterface[]
     */
    public function getLogs()
    {
        return $this->data['logs'];
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return count($this->data['logs']);
    }

    /**
     * @return int
     */
    public function getCacheHitCount()
    {
        return count(array_filter($this->data['logs'], function (LogInterface $log) {
            return $log->isCacheHit();
        }));
    }

    /**
     * @return float
     */
    public function getTimeInSeconds()
    {
        $time = 0;
        foreach ($this->data['logs'] as $log) {
            /**
             * @var LogInterface $log
             */
            $time += $log->getDurationInSeconds();
        }

        return $time;
    }

    /**
     * @return bool
     */
    public function getUsingPreviewApi()
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
    public function getName()
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
}
