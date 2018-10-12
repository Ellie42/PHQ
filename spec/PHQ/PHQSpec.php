<?php

namespace spec\PHQ;

use PhpSpec\ObjectBehavior;
use PHQ\Config\EventBusConfig;
use PHQ\Config\PHQConfig;
use PHQ\Config\StorageHandlerConfig;
use PHQ\Config\WorkerConfig;
use PHQ\Data\JobDataset;
use PHQ\EventBus\IJobEventBus;
use PHQ\EventBus\PeriodicEventBus;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\IJobEventListener;
use PHQ\Jobs\Job;
use PHQ\PHQ;
use PHQ\Workers\QueueManager;
use Prophecy\Argument;
use React\EventLoop\LoopInterface;
use spec\PHQ\Jobs\JobTest;
use spec\TestObjects\TestJob;
use spec\TestObjects\TestQueueStorage;

class PHQSpec extends ObjectBehavior
{
    /**
     * @var TestQueueStorage
     */
    private $storage;

    /**
     * @var QueueManager
     */
    private $workerManager;

    function let(TestQueueStorage $storage, QueueManager $workerManager)
    {
        $this->storage = $storage;
        $this->workerManager = $workerManager;
        $this->beConstructedWith($this->storage, null, $workerManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PHQ::class);
        $this->shouldHaveType(IJobEventListener::class);
    }

    function it_should_allow_adding_of_jobs(TestJob $job)
    {
        $this->storage->enqueue($job)->willReturn(true)->shouldBeCalled();
        $this->enqueue($job);
    }

    function it_should_use_the_config_to_initialise_all_configured_services(
        PHQConfig $config,
        StorageHandlerConfig $storageConfig,
        TestQueueStorage $queueStorage,
        WorkerConfig $workerConfig,
        EventBusConfigMock $eventBusConfig
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn($storageConfig);
        $config->getWorkerConfig()->shouldBeCalled()->willReturn($workerConfig);
        $config->getEventBusConfig()->shouldBeCalled()->willReturn($eventBusConfig);

        $storageConfig->getStorage()->shouldBeCalled()->willReturn($queueStorage);
        $eventBusConfig->getClass()->shouldBeCalled()->willReturn(PeriodicEventBus::class);
        $eventBusConfig->getOptions()->shouldBeCalled()->willReturn([]);

        $this->getStorageHandler()->shouldReturn($queueStorage);
    }

    function it_should_allow_you_to_perform_inital_setup_for_storage_handlers(
        PHQConfig $config,
        StorageHandlerConfig $storageConfig,
        TestQueueStorage $queueStorage,
        WorkerConfig $workerConfig,
        EventBusConfigMock $eventBusConfig
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn($storageConfig);
        $config->getWorkerConfig()->shouldBeCalled()->willReturn($workerConfig);
        $config->getEventBusConfig()->shouldBeCalled()->willReturn($eventBusConfig);

        $workerConfig->getScriptCommand()->willReturn("");
        $storageConfig->getStorage()->shouldBeCalled()->willReturn($queueStorage);
        $eventBusConfig->getClass()->shouldBeCalled()->willReturn(PeriodicEventBus::class);
        $eventBusConfig->getOptions()->shouldBeCalled()->willReturn([]);

        $queueStorage->setup(Argument::any())->shouldBeCalled();

        $this->shouldNotThrow(\Exception::class)->during('setup');
    }

    function it_should_throw_an_error_if_no_storage_has_been_configured_or_set(
        PHQConfig $config
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn(null);
        $this->shouldThrow(ConfigurationException::class)->duringInstantiation();
    }

    function it_should_be_able_to_update_a_job_status()
    {
        $jobData = new JobDataset([
            "id" => 1,
        ]);

        $job = new TestJob($jobData);

        $this->storage->update($job->getData())->shouldBeCalled()->willReturn(true);

        $this->update($job)->shouldReturn(true);
    }

    function it_should_be_able_to_start_the_worker_processes(LoopInterface $loop)
    {
        $this->beConstructedWith($this->storage, null, $this->workerManager, null, $loop);
        $this->workerManager->startWorking($loop)->shouldBeCalled();
        $this->start();
    }

    function it_should_notify_worker_manager_of_new_jobs_without_id()
    {
        $this->workerManager->assignNewJobs()->shouldBeCalled();
        $this->onJobAdded();
    }

    function it_should_notify_worker_manager_when_a_full_job_list_update_is_requested()
    {
        $this->workerManager->assignNewJobs()->shouldBeCalled();
        $this->updateJobs();
    }

    function it_should_fail_to_set_the_event_bus_if_it_isnt_valid(PHQConfig $config, EventBusConfigMock $eventBusConfig, QueueManager $workerManager)
    {
        $this->beConstructedWith($this->storage, $config, $workerManager);
        $config->getEventBusConfig()->shouldBeCalled()->willReturn($eventBusConfig);
        $eventBusConfig->getClass()->shouldBeCalled()->willReturn("abc");
        $this->shouldThrow(ConfigurationException::class)->duringInstantiation();
    }

    function it_should_use_the_existing_event_bus_if_provided(PHQConfig $config, IJobEventBus $eventBus, QueueManager $workerManager){
        $this->beConstructedWith($this->storage, $config, $workerManager,$eventBus);
        $this->getEventBus()->shouldBe($eventBus);
    }
}

class JobNotGoodEnough
{

}

class EventBusConfigMock extends EventBusConfig
{
    public function getClass()
    {
    }

    public function getOptions()
    {

    }
}