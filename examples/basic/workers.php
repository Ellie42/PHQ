<?php

require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * This is the worker process script, this is how you will get PHQ to start processing the queue and
 * running the jobs on different worker processes
 */
$phq = new \PHQ\PHQ();

$phq->start();