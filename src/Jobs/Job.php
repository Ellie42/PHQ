<?php

namespace PHQ\Jobs;

use PHQ\Data\Dataset;
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
     * Serialise the jobs dataset
     * @return string
     */
    function serialise(): string
    {
        $data = $this->data;
        return json_encode($data->toArray());
    }

    /**
     * Deserialise data and create/hydrate the job dataset
     * @param string $data
     */
    function deserialise(string $data): void
    {
        $this->data->hydrate(json_decode($data, true));
    }

    public function getData() : Dataset
    {
        return $this->data;
    }
}