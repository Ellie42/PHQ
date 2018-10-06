<?php

namespace spec\PHQ\Jobs;

use PHQ\Data\Dataset;
use PHQ\Data\JobDataset;
use PHQ\Jobs\Job;
use PhpSpec\ObjectBehavior;

class JobSpec extends ObjectBehavior
{
    function let(){
        $this->beAnInstanceOf(JobTest::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Job::class);
    }

    function it_should_create_a_new_dataset_if_none_is_provided(){
        $this->beConstructedWith(null);
        $this->getData()->shouldBeAnInstanceOf(JobDataset::class);
    }

    function it_should_accept_an_existing_dataset_instead_of_creating_its_own(JobDataset $data){
        $this->beConstructedWith($data);
        $this->getData()->shouldBe($data);
    }

    function it_should_be_able_to_serialise_the_dataset(JobDataset $data){
        $arrayData = [
            "a" => 1
        ];

        $this->beConstructedWith($data);

        $data->toArray()->shouldBeCalled()->willReturn($arrayData);

        $this->serialise()->shouldBe(json_encode($arrayData));
    }

    function it_should_be_able_to_create_a_new_dataset_from_serialised_data(){
        $this->beConstructedWith(null);
        $this->deserialise(json_encode([]));

        $this->getData()->shouldBeAnInstanceOf(JobDataset::class);
    }

    function it_should_hydrate_existing_dataset_with_deserialised_data(JobDataset $data){
        $arrayData = [
            "a" => 1
        ];

        $this->beConstructedWith($data);
        $data->hydrate($arrayData)->shouldBeCalled();

        $this->deserialise(json_encode($arrayData));
        $this->getData()->shouldReturn($data);
    }
}

class JobTest extends Job{

    /**
     * This should return a status number as defined in {Job::class}
     * @return int
     */
    function run(): int
    {
        // TODO: Implement run() method.
    }

}
