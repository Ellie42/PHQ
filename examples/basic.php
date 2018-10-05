<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/MakeSomeFilesJob.php";

$phq = new \PHQ\PHQ();
$job = new MakeSomeFilesJob("./files", 10);

$phq->enqueue($job);

$job = $phq->getNext();