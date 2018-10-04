<?php

$storage = new \PHQ\Storage\FileQueueStorage();
$phq = new \PHQ\PHQ($storage);
$job = new MakeSomeFilesJob("./files", 10);

$phq->enqueue($job);