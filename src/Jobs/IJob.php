<?php

namespace PHQ\Jobs;

use PHQ\Data\Dataset;
use PHQ\Data\JobDataset;

interface IJob
{
    /**
     * Serialise the model data and return it
     * @return string
     */
    function serialise(): string;

    /**
     * Deserialise and set the data in the model
     * @param string $data
     */
    function deserialise(string $data): void;

    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     */
    function run(): int;

    /**
     * @return Dataset
     */
    function getData() : JobDataset;
}