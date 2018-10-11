<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 11/10/2018
 * Time: 15:01
 */

namespace PHQ\Messages;


interface IMessageParser
{
    function parse(string $data) : WorkerMessage;
}