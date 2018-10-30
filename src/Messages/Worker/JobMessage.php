<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 30/10/2018
 * Time: 09:15
 */

namespace PHQ\Messages\Worker;


use PHQ\Messages\WorkerMessage;

class JobMessage extends WorkerMessage
{
    /**
     * @var int | string
     */
    public $jobId;
}