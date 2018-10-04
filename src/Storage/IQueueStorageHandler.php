<?php

namespace PHQ\Storage;


use PHQ\Jobs\IJob;
use PHQ\Jobs\JobDataset;

interface IQueueStorageHandler
{

    /**
     * Retrieve a job entry
     * @param $id
     * @return JobDataset
     */
    public function get($id): ?JobDataset;

    /**
     * Save job data
     * @param IJob $job
     * @return bool
     */
    public function enqueue(IJob $job): bool;

    /**
     * Get next job in queue
     * @return JobDataset
     */
    public function getNext(): ?JobDataset;
}