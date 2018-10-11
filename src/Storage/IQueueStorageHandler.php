<?php

namespace PHQ\Storage;


use PHQ\Data\JobDataset;
use PHQ\Data\JobDatasetArray;
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
     * Return all inactive jobs
     * @param null $afterId
     * @return JobDatasetArray | JobDataset[]
     */
    public function getAvailable($afterId = null): JobDatasetArray;
}