<?php

namespace spec\PHQ\Data;

use PhpSpec\ObjectBehavior;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\Job;
use spec\TestObjects\TestJob;

class JobDatasetSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf(JobDatasetTest::class);
    }

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

    function it_should_throw_error_if_job_class_does_not_exist_when_creating()
    {
        $this->setClass('notarealjob');
        $this->shouldThrow(PHQException::class)->during('getJob');
    }

    function it_should_throw_error_if_job_class_is_not_a_valid_job()
    {
        $this->setClass(JobNotGoodEnough::class);
        $this->shouldThrow(PHQException::class)->during('getJob');
    }

    function it_should_be_able_to_create_a_job_object_from_job_data()
    {
        $this->setClass(TestJob::class);
        $this->getJob()->shouldBeAnInstanceOf(TestJob::class);
    }

    function it_should_parse_a_json_payload_when_set()
    {
        $rawPayload = ["a" => "b"];
        $payload = json_encode($rawPayload);
        $this->setPayload($payload);

        $this->getPayload()->shouldReturn($rawPayload);
    }
}

class JobDatasetTest extends JobDataset
{
    public function getPayload()
    {
        return $this->payload;
    }
}


class JobNotGoodEnough
{

}