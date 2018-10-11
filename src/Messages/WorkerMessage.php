<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 11/10/2018
 * Time: 13:49
 */

namespace PHQ\Messages;


class WorkerMessage
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

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Serialise the message
     * @return false|string
     */
    public function serialise()
    {
        return json_encode([
            "type" => $this->type,
            "data" => $this->data
        ]);
    }
}