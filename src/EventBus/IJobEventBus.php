<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 09/10/2018
 * Time: 10:07
 */

namespace PHQ\EventBus;


use PHQ\Config\EventBusConfig;
use PHQ\Jobs\IJobEventListener;
use React\EventLoop\LoopInterface;

interface IJobEventBus
{
    function __construct(IJobEventListener $workerManager, EventBusConfig $config);

    /**
     * Start the job event bus, listen for new job events and invoke methods on the job event listener
     * @param LoopInterface $loop
     * @return mixed
     */
    function start(LoopInterface $loop);
}