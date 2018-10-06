<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/MakeSomeFilesJob.php";
require_once __DIR__ . "/MakeSomeFilesPayload.php";

$phq = new \PHQ\PHQ();

$payload = new MakeSomeFilesPayload();

$payload->setDir("./files");
$payload->setFileCount(10);

$job = new MakeSomeFilesJob($payload);

$phq->enqueue($job);

$job = $phq->getNext();

$job->run();