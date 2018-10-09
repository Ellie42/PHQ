<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 09/10/2018
 * Time: 09:59
 */

namespace PHQ\EventBus;


use PHQ\Config\EventBusConfig;
use PHQ\Config\Options\PeriodicEventBusOptions;
use PHQ\Jobs\IJobEventListener;
use PHQ\Workers\WorkerManager;
use React\EventLoop\LoopInterface;

class PeriodicEventBus implements IJobEventBus
{
    /**
     * @var IJobEventListener
     */
    protected $eventListener;

    /**
     * @var PeriodicEventBusOptions
     */
    protected $options;

    public function __construct(IJobEventListener $eventListener, EventBusConfig $config)
    {
        $this->options = new PeriodicEventBusOptions($config->getOptions());
        $this->eventListener = $eventListener;
    }

    /**
     * Start the job event bus, listen for new job events and invoke methods on the job event listener
     * @param LoopInterface $loop
     * @return mixed
     */
    function start(LoopInterface $loop)
    {
        $loop->addPeriodicTimer($this->options->getInterval(), function () {
            $this->eventListener->updateJobs();
        });
    }
}