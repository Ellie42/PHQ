<?php

namespace PHQExamples\Jobs;

class MakeSomeFilesJob extends \PHQ\Jobs\Job{
    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     * @throws \PHQ\Exceptions\PHQException
     */
    function run(): int
    {
        //You can access the raw payload by calling $this->data->getPayload();
        //However using this method you can ensure that the dataset the job is passed is correct.
        $payload = $this->getPayload(MakeSomeFilesPayload::class);

        //TODO implement job!
        var_dump($payload);

        //Return a status to tell the storage handler what to update the status to.
        return \PHQ\Jobs\Job::STATUS_SUCCESS;
    }
}