<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 11/10/2018
 * Time: 13:49
 */

namespace PHQ\Messages;


use PHQ\Data\Dataset;

class WorkerMessage extends Dataset
{
    /**
     * Class name of message type
     * @var string
     */
    public $type = WorkerMessage::class;

    /**
     * Data to send to the worker
     * @var array
     */
    public $data;

    /**
     * Serialise the message
     * @return false|string
     */
    public function serialise()
    {
        return json_encode($this->toArray());
    }
}