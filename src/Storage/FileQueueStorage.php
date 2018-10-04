<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 11:40
 */

namespace PHQ\Storage;


use PHQ\Jobs\IJob;
use PHQ\Jobs\JobDataset;

class FileQueueStorage implements IQueueStorageHandler
{

    /**
     * @param IJob $job
     * @return bool
     */
    public function enqueue(IJob $job): bool
    {
        // TODO: Implement save() method.
    }

    /**
     * @param $id
     * @return JobDataset
     */
    public function get($id): JobDataset
    {
        // TODO: Implement get() method.
    }

    /**
     * @return JobDataset
     */
    public function getNext(): JobDataset
    {
        // TODO: Implement getNext() method.
    }
}