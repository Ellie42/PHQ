<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 11:28
 */

namespace spec\TestObjects;


use PHQ\Jobs\Job;

class TestJob extends Job
{
    function run(): int
    {
        return self::STATUS_SUCCESS;
    }

    function deserialise(string $data): void
    {
        // TODO: Implement deserialise() method.
    }
}