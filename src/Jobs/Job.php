<?php

namespace PHQ\Jobs;

abstract class Job implements IJob
{
    public const STATUS_SUCCESS = 1;
    public const STATUS_IDLE = 0;
    public const STATUS_FAILURE = -1;

    protected $data = [];

    function serialise(): string
    {
        $data = $this->data;
        return json_encode($data);
    }

    function deserialise(string $data): void
    {
        $this->data = json_decode($data, true);
    }

    static function fromJobEntry(JobDataset $jobData): Job
    {
        if (!class_exists($jobData->className)) {
            throw new \Exception("Class {$jobData->className} does not exist!");
        }

        $className = $jobData->className;

        $obj = new $className();

//        if(!($obj instanceof ))

        return $obj;
    }
}