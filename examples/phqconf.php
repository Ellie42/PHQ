<?php

return [
    "storage" => [
        "handler" => \PHQ\Storage\MySQLQueueStorage::class,
        "options" => [
            "development" => [
                "host" => "localhost",
                "port" => 7777,
                "user" => "root",
                "pass" => "root",
                "database" => "phq_test"
            ]
        ]
    ],
];
