<?php

namespace PHQ\Jobs;

use PHQ\Data\Dataset;
use PHQ\Data\JobDataset;
use PHQ\Data\Payload;
use PHQ\Exceptions\PHQException;

abstract class Job implements IJob
{
    public const STATUS_SUCCESS = 1;
    public const STATUS_IDLE = 0;
    public const STATUS_FAILURE = -1;

    /**
     * @var JobDataset
     */
    protected $data;

    /**
     * If passing a payload object then only the payload property will be set
     * otherwise all properties will be set
     * Job constructor.
     * @param Dataset|null|JobDataset|Payload $dataset
     */
    public function __construct(Dataset $dataset = null)
    {
        if ($dataset instanceof Payload) {
            $this->data = new JobDataset([
                "payload" => $dataset->toArray()
            ]);
        } else if ($dataset === null) {
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

    public function getData(): JobDataset
    {
        return $this->data;
    }

    /**
     * Return the payload as an instance of the requested class.
     * I wish php had generics D:
     * @param string $payloadClass
     * @return Payload
     * @throws PHQException
     */
    public function getPayload(string $payloadClass): Payload
    {
        if (!is_subclass_of($payloadClass, Payload::class)) {
            throw new PHQException("$payloadClass is not a valid payload!");
        }

        return new $payloadClass($this->data->getPayload());
    }
}