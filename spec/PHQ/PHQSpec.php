<?php

namespace spec\PHQ;

use PhpSpec\ObjectBehavior;
use PHQ\Config\PHQConfig;
use PHQ\Config\StorageHandlerConfig;
use PHQ\Config\WorkerConfig;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\PHQ;
use Prophecy\Argument;
use spec\PHQ\Jobs\JobTest;
use spec\TestObjects\TestJob;
use spec\TestObjects\TestQueueStorage;

class PHQSpec extends ObjectBehavior
{
    /**
     * @var TestQueueStorage
     */
    private $storage;

    function let(TestQueueStorage $storage)
    {
        $this->storage = $storage;
        $this->beConstructedWith($storage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PHQ::class);
    }

    function it_should_allow_adding_of_jobs(TestJob $job)
    {
        $this->storage->enqueue($job)->willReturn(true)->shouldBeCalled();
        $this->enqueue($job);
    }

    function it_should_be_able_to_get_the_next_job()
    {
        $jobData = new JobDataset([
            "id" => 5,
            "class" => TestJob::class,
            "payload" => "{}"
        ]);
        $this->storage->getNext()->shouldBeCalled()->willReturn($jobData);

        $this->getNext()->shouldBeAnInstanceOf(IJob::class);
    }

    function it_should_use_the_config_to_initialise_all_configured_services(
        PHQConfig $config,
        StorageHandlerConfig $storageConfig,
        TestQueueStorage $queueStorage,
        WorkerConfig $workerConfig
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn($storageConfig);
        $config->getWorkerConfig()->shouldBeCalled()->willReturn($workerConfig);
        $workerConfig->getScriptCommand()->willReturn("");

        $storageConfig->getStorage()->shouldBeCalled()->willReturn($queueStorage);

        $this->getStorageHandler()->shouldReturn($queueStorage);
    }

    function it_should_throw_an_error_if_no_storage_has_been_configured_or_set(
        PHQConfig $config
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn(null);
        $this->shouldThrow(ConfigurationException::class)->duringInstantiation();
    }


    function it_should_allow_you_to_perform_inital_setup_for_storage_handlers(
        PHQConfig $config,
        StorageHandlerConfig $storageConfig,
        TestQueueStorage $queueStorage,
        WorkerConfig $workerConfig
    )
    {
        $this->beConstructedWith(null, $config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn($storageConfig);
        $config->getWorkerConfig()->shouldBeCalled()->willReturn($workerConfig);
        $storageConfig->getStorage()->shouldBeCalled()->willReturn($queueStorage);
        $workerConfig->getScriptCommand()->willReturn("");
        $queueStorage->setup(Argument::any())->shouldBeCalled();

        $this->shouldNotThrow(\Exception::class)->during('setup');
    }

    function it_should_throw_error_if_job_class_does_not_exist_when_creating()
    {
        $jobDataset = new JobDataset([
            "class" => "notarealjob"
        ]);

        $this->shouldThrow(PHQException::class)->during('createJobFromJobEntry', [$jobDataset]);
    }

    function it_should_throw_error_if_job_class_is_not_a_valid_job()
    {
        $jobDataset = new JobDataset([
            "class" => JobNotGoodEnough::class
        ]);

        $this->shouldThrow(PHQException::class)->during('createJobFromJobEntry', [$jobDataset]);
    }

    function it_should_be_able_to_create_a_job_object_from_job_data()
    {
        $this->createJobFromJobEntry(new JobDataset([
            "class" => TestJob::class
        ]))->shouldBeAnInstanceOf(TestJob::class);
    }

    function it_should_be_able_to_update_a_job_status(){
        $jobData = new JobDataset([
            "id" => 1,
        ]);

        $job = new TestJob($jobData);

        $this->storage->update($job->getData())->shouldBeCalled()->willReturn(true);

        $this->update($job)->shouldReturn(true);
    }

    function it_should_run_the_next_job_and_update_status_when_complete(Job $job){
        $dataset = new JobDataset([
            "class" => TestJob::class
        ]);

        $this->storage->getNext()->shouldBeCalled()->willReturn($dataset);
        $this->storage->update($dataset)->shouldBeCalled();
        $this->process();
    }

    //TODO implement
    function it_should_be_able_to_run_all_jobs_automatically_deferring_to_worker_processes(){
        $this->start()->shouldReturn(true);
    }
}

class JobNotGoodEnough
{

}
