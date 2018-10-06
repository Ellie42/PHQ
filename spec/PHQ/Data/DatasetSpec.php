<?php

namespace spec\PHQ\Data;

use PhpSpec\ObjectBehavior;
use PHQ\Data\Dataset;

class DatasetSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Dataset::class);
    }

    function it_should_set_requested_properties_if_they_exist()
    {
        $this->useTestDataset();

        $this->hydrate([
            "a" => "test"
        ], ["a"]);

        $this->getA()->shouldReturn("test");
    }

    function it_should_throw_an_error_if_a_property_does_not_exist()
    {
        $this->shouldThrow(\BadMethodCallException::class)
            ->during("hydrate", [
                ["a" => "test"],
                ["a"]
            ]);
    }

    function it_should_call_a_setter_during_hydration_if_it_exists()
    {
        $this->useTestDataset();

        $this->hydrate([
            "setter" => "abc"
        ], ["setter"]);

        $this->getSetter()->shouldReturn("nope!");
    }

    function it_should_allow_setters_and_getters_for_any_property_to_be_called()
    {
        $this->useTestDataset();

        $this->hydrate(["a" => "test"]);

        $this->getA()->shouldReturn("test");

        $this->setA("another test");

        $this->getA()->shouldReturn("another test");
    }

    function it_should_throw_error_if_calling_setter_with_no_values(){
        $this->useTestDataset();
        $this->shouldThrow(\BadMethodCallException::class)->during('setA');
    }

    function it_should_be_able_to_convert_all_data_to_an_array_ignoring_unset_values(){
        $data = ["a" => 1, "b" => 2];

        $this->useTestDataset();
        $this->beConstructedWith($data);
        $this->toArray()->shouldReturn([
            "a" => 1,
            "b" => 2,
        ]);
    }

    private function useTestDataset(): void
    {
        $this->beAnInstanceOf(TestDataset::class);
    }
}

class TestDataset extends Dataset
{
    public $a;
    public $b;
    public $setter;

    public function hydrate(array $props, array $whitelistKeys = [])
    {
        parent::hydrate($props, $whitelistKeys);
    }

    public function setSetter(string $value)
    {
        $this->setter = 'nope!';
    }
}
