<?php

namespace spec\PHQ;

use PhpSpec\ObjectBehavior;
use PHQ\Jobs\IJob;
use PHQ\Jobs\JobDataset;
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
        $jobData = new JobDataset(5, TestJob::class, '123');
        $this->storage->getNext()->shouldBeCalled()->willReturn($jobData);

        $this->getNext()->shouldBeAnInstanceOf(IJob::class);
    }
}
