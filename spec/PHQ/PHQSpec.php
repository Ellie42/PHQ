<?php

namespace spec\PHQ;

use PhpSpec\ObjectBehavior;
use PHQ\Config\PHQConfig;
use PHQ\Config\StorageHandlerConfig;
use PHQ\Data\JobDataset;
use PHQ\Jobs\IJob;
use PHQ\PHQ;
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

    function it_should_use_the_storage_config_to_create_a_storage_handler_instance(
        PHQConfig $config,
        StorageHandlerConfig $storageConfig,
        TestQueueStorage $queueStorage
    ){
        $this->beConstructedWith(null,$config);
        $config->getStorageConfig()->shouldBeCalled()->willReturn($storageConfig);
        $storageConfig->getStorage()->shouldBeCalled()->willReturn($queueStorage);
        $this->getStorageHandler()->shouldReturn($queueStorage);
    }
}
