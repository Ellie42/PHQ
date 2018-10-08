<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:30
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;

interface IWorker
{
    /**
     * Should run the job and return a status code defined in \PHQ\Jobs\Job::STATUS_*
     * @param JobDataset $data
     * @return int
     */
    function work(JobDataset $data): int;
}