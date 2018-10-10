<?php

namespace spec\PHQ\Workers;

use PHQ\Config\WorkerConfig;
use PHQ\Exceptions\PHQException;
use PHQ\PHQ;
use PHQ\Workers\WorkerContainer;
use PHQ\Workers\WorkerContainerArray;
use PHQ\Workers\WorkerManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class WorkerManagerSpec extends ObjectBehavior
{
    /**
     * @var WorkerConfig
     */
    private $config;

    /**
     * @var LoopInterface
     */
    private $loop;

    function let(WorkerConfig $workerConfig, PHQ $phq, LoopInterface $loop){
        $this->config = $workerConfig;
        $this->loop = $loop;
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

    function it_should_allow_a_worker_container_factory_to_be_assigned(){
        $factory = function(Process $process){

        };

        $this->setWorkerContainerFactory($factory);
        $this->getWorkerContainerFactory()->shouldBe($factory);
    }

    function it_should_be_able_to_start_the_worker_event_loop(WorkerContainer $container){
        $container->start($this->loop)->shouldBeCalled();
        $this->setWorkerContainerFactory(function (Process $process) use (&$container) {
            return $container->getWrappedObject();
        });

        $this->loop->addPeriodicTimer(Argument::any(), Argument::type(\Closure::class))->shouldBeCalled();
        $this->loop->run()->shouldBeCalled();
        $this->startWorking($this->loop);
    }
}
