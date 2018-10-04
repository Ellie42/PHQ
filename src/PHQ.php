<?php

namespace PHQ;

use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageHandler;

class PHQ
{
    /**
     * @var IQueueStorageHandler
     */
    private $storageHandler;

    public function __construct(IQueueStorageHandler $storageHandler)
    {
        $this->storageHandler = $storageHandler;
    }

    public function enqueue(IJob $job)
    {
        $this->storageHandler->enqueue($job);
    }

    /**
     * Return the next job from the queue
     * @return IJob
     * @throws \Exception
     */
    public function getNext(): IJob
    {
        $job = $this->storageHandler->getNext();

        return Job::fromJobEntry($job);
    }
}