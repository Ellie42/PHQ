<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 12:54
 */

namespace PHQ\Data;


class JobDataset extends Dataset
{
    /**
     * Job ID
     */
    public $id;

    /**
     * Classname of the job object that this entry was created from
     * @var string
     */
    public $class;

    /**
     * Deserialised Job Payload
     * @var array
     */
    public $payload;

    /**
     * Current status of the job, see: PHQ\Jobs\Job::STATUS_*
     * @var int
     */
    public $status;

    /**
     * Number of times this job has failed and been re-run
     * @var int
     */
    public $retries;

    public function getSerialisedPayload()
    {
        return json_encode($this->payload);
    }

}