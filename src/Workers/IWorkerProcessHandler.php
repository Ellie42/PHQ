<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 11:06
 */

namespace PHQ\Workers;


interface IWorkerProcessHandler
{
    function onData($chunk);

    function onExit($code, $signal);

    function onError(\Exception $e);
}