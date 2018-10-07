<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/MakeSomeFilesJob.php";
require_once __DIR__ . "/MakeSomeFilesPayload.php";

$phq = new \PHQ\PHQ();
$payload = new MakeSomeFilesPayload();

//Setting up the payload that will be used when the job is run
$payload->setDir("./files");
$payload->setFileCount(10);

//Create the job instance and set the payload
$job = new MakeSomeFilesJob($payload);

//Add the job to the queue, this will use the configured storage handler to save all the job data
$phq->enqueue($job);

//Retrieve the next job off the queue using the storage handler
$job = $phq->getNext();

//Run the job
$status = $job->run();

//Set the new job status
$job->getData()->setStatus($status);

//Update job status in storage
$phq->update($job);
