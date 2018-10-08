<?php

namespace spec\PHQ\Workers;

use PHQ\Config\WorkerConfig;
use PHQ\Exceptions\PHQException;
use PHQ\PHQ;
use PHQ\Workers\WorkerContainerArray;
use PHQ\Workers\WorkerManager;
use PhpSpec\ObjectBehavior;

class WorkerManagerSpec extends ObjectBehavior
{
    /**
     * @var WorkerConfig
     */
    private $config;

    function let(WorkerConfig $workerConfig, PHQ $phq){
        $this->config = $workerConfig;
        $this->config->getScriptCommand()->willReturn("");
        $this->beConstructedWith($workerConfig, $phq);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerManager::class);
    }

    function it_should_be_able_to_spawn_worker_processes(){
        $this->shouldNotThrow(PHQException::class)->during('startWorking');

        $this->getWorkerContainers()->shouldBeAnInstanceOf(WorkerContainerArray::class);
    }
}
