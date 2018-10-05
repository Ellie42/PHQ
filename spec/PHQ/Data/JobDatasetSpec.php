<?php

namespace spec\PHQ\Data;

use PHQ\Data\JobDataset;
use PhpSpec\ObjectBehavior;
use PHQ\Jobs\Job;
use Prophecy\Argument;
use spec\TestObjects\TestJob;

class JobDatasetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JobDataset::class);
    }

    function it_should_set_all_job_properties_properly()
    {
        $this->beConstructedWith([
            "id" => 1,
            "class" => TestJob::class,
            "payload" => "abc",
            "status" => Job::STATUS_SUCCESS
        ]);

        $this->getClass()->shouldReturn(TestJob::class);
        $this->getPayload()->shouldReturn("abc");
        $this->getStatus()->shouldReturn(Job::STATUS_SUCCESS);
        $this->getId()->shouldReturn(1);
    }
}
