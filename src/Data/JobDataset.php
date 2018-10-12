<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 12:54
 */

namespace PHQ\Data;


use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;

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

    /**
     * Ensure that the payload being set is an array
     * @param $payload string | array
     */
    public function setPayload($payload)
    {
        if (is_string($payload)) {

            $parsed = json_decode($payload, true);

            //If the string was not valid json then assume it is not meant to be
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->payload = $parsed;
                return;
            }
        }

        $this->payload = $payload;
    }

    public function getSerialisedPayload()
    {
        return json_encode($this->payload);
    }

    /**
     * Returns an instance of IJob based on the class name in the JobDataset and sets the payload
     * @return IJob
     * @throws PHQException
     */
    public function getJob(): IJob
    {
        $className = $this->getClass();

        if (!class_exists($className)) {
            throw new PHQException("Class {$className} does not exist!");
        }

        if (!(is_subclass_of($className, IJob::class))) {
            throw new PHQException("$className is not an instance of " . IJob::class);
        }

        /**
         * @var IJob
         */
        $obj = new $className($this);

        return $obj;
    }
}