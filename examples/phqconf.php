<?php

return [
    "environment" => "development",
    "storage" => [
        "handler" => \PHQ\Storage\MySQL\MySQLQueueStorage::class,
        "options" => [
            "development" => [
                "host" => "127.0.0.1",
                "port" => 7777,
                "user" => "root",
                "pass" => "root",
                "database" => "phq_test"
            ]
        ]
    ],
];
