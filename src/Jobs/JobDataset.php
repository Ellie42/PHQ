<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 12:54
 */

namespace PHQ\Jobs;


class JobDataset
{
    /**
     * Job ID
     */
    public $id;

    /**
     * Classname of the job object that this entry was created from
     * @var string
     */
    public $className;

    /**
     * Serialised job payload
     * @var string
     */
    public $payload;

    /**
     * Current status of the job, see: PHQ\Jobs\Job::STATUS_*
     * @var int
     */
    public $status;

    public function __construct($id, string $className, string $payload, int $status = Job::STATUS_IDLE)
    {
        $this->id = $id;
        $this->className = $className;
        $this->payload = $payload;
        $this->status = $status;
    }
}