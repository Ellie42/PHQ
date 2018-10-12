<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 12/10/2018
 * Time: 10:58
 */

namespace PHQ\Messages\Worker;


use PHQ\Messages\WorkerMessage;

class JobFinishedMessage extends WorkerMessage
{
    public $type = self::class;

    /**
     * The job finishing status
     * @var int
     */
    public $status = \PHQ\Jobs\Job::STATUS_SUCCESS;
}