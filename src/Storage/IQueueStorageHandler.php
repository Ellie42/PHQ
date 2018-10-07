<?php

namespace PHQ\Storage;


use PHQ\Data\JobDataset;
use PHQ\Jobs\IJob;

/**
 * Objects implementing this interface can be used a storage handler for the job data storage
 * Interface IQueueStorageHandler
 * @package PHQ\Storage
 */
interface IQueueStorageHandler
{
    /**
     * Retrieve a job entry by id
     * @param $id
     * @return JobDataset
     */
    public function get($id): ?JobDataset;

    /**
     * Update the job entry
     * @param JobDataset $jobDataset
     * @return bool
     */
    public function update(JobDataset $jobDataset): bool;

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