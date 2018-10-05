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
use PHQ\Jobs\IJob;
use PHQ\Storage\IQueueStorageHandler;

class TestQueueStorage implements IQueueStorageHandler
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
}