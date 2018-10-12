<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 11/10/2018
 * Time: 13:50
 */

namespace PHQ\Messages\Container;


use PHQ\Messages\WorkerMessage;

class JobStartMessage extends WorkerMessage
{
    public $type = self::class;
}