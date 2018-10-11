<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 11:41
 */

namespace spec\TestObjects;


use PHQ\Config\PHQConfig;
use PHQ\Data\JobDataset;
use PHQ\Data\JobDatasetArray;
use PHQ\Jobs\IJob;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Storage\IQueueStorageNeedsSetup;

class TestQueueStorage implements IQueueStorageHandler, IQueueStorageNeedsSetup
{
    public function enqueue(IJob $job) : bool
    {
        return true;
    }

    /**
     * Retrieve a job entry
     * @param $id
     * @return JobDataset
     */
    public function get($id): JobDataset
    {
        // TODO: Implement get() method.
    }

    /**
     * Get next job in queue
     * @return IJob
     */
    public function getNext(): JobDataset
    {
        // TODO: Implement getNext() method.
    }

    /**
     * Initialise the storage handler with the config
     * @param PHQConfig $config
     */
    public function init(PHQConfig $config): void
    {
        // TODO: Implement init() method.
    }

    /**
     * This method will be called for the initial application environment setup required to make this storage
     * adapter function.
     */
    public function setup(): void
    {
        // TODO: Implement setup() method.
    }

    public function update(JobDataset $jobDataset): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * Return all inactive jobs
     * @param null $afterId
     * @return JobDataset[]
     */
    public function getAvailable($afterId = null): JobDatasetArray
    {
        // TODO: Implement getAvailable() method.
    }
}