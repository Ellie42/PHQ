<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 11/10/2018
 * Time: 15:00
 */

namespace PHQ\Messages;


use PHQ\Exceptions\PHQException;

class MessageParser implements IMessageParser
{
    function parse(string $data): WorkerMessage
    {
        $parsed = json_decode($data, true);

        //Job dataset should be set as the 'data' property, if it is not set then assume the message
        //does not require a body
        $sentData = isset($parsed['data']) ? $parsed['data'] : [];

        if (!isset($parsed['type'])) {
            throw new PHQException("Must provide a type property with messages!");
        }

        $type = $parsed['type'];

        if (!is_subclass_of($type, WorkerMessage::class)) {
            throw new PHQException("Provided type is not a subclass of " . WorkerMessage::class);
        }

        return new $type($sentData);
    }
}