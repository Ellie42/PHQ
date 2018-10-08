<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../Jobs/MakeSomeFilesJob.php";
require_once __DIR__ . "/../Jobs/MakeSomeFilesPayload.php";

/**
 * This is the application where new jobs will be configured and added to the queue but will not
 * be processed.
 *
 * Both the application setting up PHQ and the worker process should use the same phqconf.php
 */
$phq = new \PHQ\PHQ();
$payload = new MakeSomeFilesPayload();

//Setting up the payload that will be used when the job is run
$payload->setDir("./files");
$payload->setFileCount(10);

//Create the job instance and set the payload
$job = new MakeSomeFilesJob($payload);

//Add the job to the queue, this will use the configured storage handler to save all the job data
$phq->enqueue($job);