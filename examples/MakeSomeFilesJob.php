<?php

class MakeSomeFilesJob extends \PHQ\Jobs\Job{
    public function  __construct(string $directory, int $fileCount){
        $this->data['dir'] = $directory;
        $this->data['fileCount'] = $fileCount;
    }

    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     */
    function run(): int
    {
        // TODO: Implement run() method.
    }
}