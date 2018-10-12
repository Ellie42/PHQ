<?php

require_once __DIR__ . "/../vendor/autoload.php";

$worker = new \PHQ\Workers\Worker(new \PHQ\Messages\MessageParser());

$worker->start();