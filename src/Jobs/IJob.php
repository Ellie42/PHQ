<?php

namespace PHQ\Jobs;

interface IJob
{
    function serialise(): string;

    function deserialise(string $data): void;

    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     */
    function run(): int;
}