<?php

namespace spec\PHQ\Workers;

use PHQ\Config\WorkerConfig;
use PHQ\Data\JobDataset;
use PHQ\Data\JobDatasetArray;
use PHQ\Exceptions\PHQException;
use PHQ\PHQ;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Workers\IWorkerCommunicator;
use PHQ\Workers\WorkerContainer;
use PHQ\Workers\WorkerContainerArray;
use PHQ\Workers\QueueManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class QueueManagerSpec extends ObjectBehavior
{
    /**
     * @var WorkerConfig
     */
    private $config;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var PHQ
     */
    private $phq;

    /**
     * @var IQueueStorageHandler
     */
    private $storage;

    function let(WorkerConfigTest $workerConfig, PHQ $phq, LoopInterface $loop, IQueueStorageHandler $storageHandler)
    {
        $this->config = $workerConfig;
        $this->loop = $loop;
        $this->phq = $phq;
        $this->storage = $storageHandler;

        $this->config->getScriptCommand()->willReturn("");
        $this->config->getCount()->willReturn(1);
        $this->phq->getStorageHandler()->willReturn($storageHandler);
        $this->beConstructedWith($workerConfig, $phq);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueueManager::class);
    }

    function it_should_be_able_to_spawn_worker_processes()
    {
        $this->shouldNotThrow(PHQException::class)->during('startWorking');

        $this->getWorkerContainers()->shouldBeAnInstanceOf(WorkerContainerArray::class);
    }

    function it_should_use_a_provided_communicator(IWorkerCommunicator $communicator)
    {
        $this->beConstructedWith($this->config,$this->phq,$communicator);

        $this->getCommunicator()->shouldBe($communicator);
    }

    function it_should_be_able_to_create_workers_without_a_factory(){
        $this->storage->getAvailable(Argument::any())->willReturn(new JobDatasetArray());
        $this->startWorking($this->loop);
        expect($this->getWorkerContainers()[0]->getWrappedObject())->shouldBeAnInstanceOf(WorkerContainer::class);
    }

    function it_should_allow_a_worker_container_factory_to_be_assigned()
    {
        $factory = function (Process $process) {

        };

        $this->setWorkerContainerFactory($factory);
        $this->getWorkerContainerFactory()->shouldBe($factory);
    }

    function it_should_be_able_to_start_the_worker_event_loop(WorkerContainer $container, IQueueStorageHandler $storageHandler)
    {
        $this->config->getCount()->shouldBeCalled()->willReturn(1);
        $this->phq->getStorageHandler()->shouldBeCalled()->willReturn($storageHandler);
        $storageHandler->getAvailable(0)->shouldBeCalled()->willReturn(new JobDatasetArray());
        $this->setWorkerContainerFactory(function (Process $process, LoopInterface $loop) use (&$container) {
            return $container->getWrappedObject();
        });

        $this->loop->addPeriodicTimer(Argument::any(), Argument::type(\Closure::class))->shouldBeCalled();
        $this->loop->run()->shouldBeCalled();
        $this->startWorking($this->loop);
    }

    function it_should_attempt_to_get_new_jobs_if_the_queue_is_empty_and_cancel_if_there_are_none(WorkerContainer $container, IQueueStorageHandler $storageHandler)
    {
        $this->phq->getStorageHandler()->shouldBeCalled()->willReturn($storageHandler);

        $storageHandler->getAvailable(0)->shouldBeCalled()->willReturn(new JobDatasetArray());

        $this->setWorkerContainerFactory(function (Process $process) use (&$container) {
            return $container->getWrappedObject();
        });

        $container->giveJob(Argument::any())->shouldNotBeCalled();

        $this->assignNewJobs();
    }

    function it_should_give_remaining_jobs_to_workers_when_there_are_more_jobs_than_workers(WorkerContainer $container, IQueueStorageHandler $storageHandler)
    {
        $this->phq->getStorageHandler()->shouldBeCalled()->willReturn($storageHandler);
        $this->config->getCount()->shouldBeCalled()->willReturn(10);

        $storageHandler->getAvailable(0)->shouldBeCalled()->willReturn(new JobDatasetArray(
            [
                new JobDataset([
                    "id" => 1
                ])
            ]
        ));

        $this->setWorkerContainerFactory(function (Process $process) use (&$container) {
            return $container->getWrappedObject();
        });

        $container->hasJob()->shouldBeCalled()->willReturn(false);

        $container->giveJob(Argument::type(JobDataset::class))->shouldBeCalledOnce();

        $this->startWorking($this->loop);
    }

    function it_should_give_jobs_to_workers_when_there_are_plenty_of_jobs(WorkerContainer $container, IQueueStorageHandler $storageHandler){
        $this->phq->getStorageHandler()->shouldBeCalled()->willReturn($storageHandler);
        $this->config->getCount()->shouldBeCalled()->willReturn(1);

        $storageHandler->getAvailable(0)->shouldBeCalled()->willReturn(new JobDatasetArray(
            [
                new JobDataset([
                    "id" => 1
                ])
            ]
        ));

        $this->setWorkerContainerFactory(function (Process $process) use (&$container) {
            return $container->getWrappedObject();
        });

        $container->hasJob()->shouldBeCalled()->willReturn(false);

        $container->giveJob(Argument::type(JobDataset::class))->shouldBeCalledOnce();

        $this->startWorking($this->loop);
    }
}

class WorkerConfigTest extends WorkerConfig
{
    public function getCount()
    {

    }
}
