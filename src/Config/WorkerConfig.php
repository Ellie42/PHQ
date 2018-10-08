<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 07/10/2018
 * Time: 10:23
 */

namespace PHQ\Config;


class WorkerConfig
{
    public $workerCount = 1;

    public function __construct(int $count = 1)
    {
        $this->workerCount = $count;
    }
}