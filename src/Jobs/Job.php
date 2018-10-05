<?php

namespace PHQ\Jobs;

use PHQ\Data\JobDataset;

abstract class Job implements IJob
{
    public const STATUS_SUCCESS = 1;
    public const STATUS_IDLE = 0;
    public const STATUS_FAILURE = -1;

    /**
     * @var JobDataset
     */
    protected $data;

    public function __construct(JobDataset $dataset = null)
    {
        if ($dataset === null) {
            $this->data = new JobDataset();
        } else {
            $this->data = $dataset;
        }
    }

    /**
     * @return string
     */
    function serialise(): string
    {
        $data = $this->data;
        return json_encode($data);
    }

    /**
     * @param string $data
     */
    function deserialise(string $data): void
    {
        $this->data = json_decode($data, true);
    }

    /**
     * Returns an instance of IJob based on the class name in the JobDataset and sets the payload
     * @param JobDataset $jobData
     * @return IJob
     * @throws \Exception
     */
    static function fromJobEntry(JobDataset $jobData): IJob
    {
        if (!class_exists($jobData->class)) {
            throw new \Exception("Class {$jobData->class} does not exist!");
        }

        $className = $jobData->class;

        //Remove the class property from the dataset as the job class will reuse this dataset
        unset($jobData->class);

        if (!(is_subclass_of($className, IJob::class))) {
            throw new \Exception("$className is not an instance of " . IJob::class);
        }

        /**
         * @var IJob
         */
        $obj = new $className($jobData);

        return $obj;
    }
}