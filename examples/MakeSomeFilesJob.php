<?php

class MakeSomeFilesJob extends \PHQ\Jobs\Job{
    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     * @throws \PHQ\Exceptions\PHQException
     */
    function run(): int
    {
        $payload = $this->getPayload(MakeSomeFilesPayload::class);

        var_dump($payload);

        return \PHQ\Jobs\Job::STATUS_SUCCESS;
    }
}