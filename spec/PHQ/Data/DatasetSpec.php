<?php

namespace spec\PHQ\Data;

use PHQ\Data\Dataset;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    private function useTestDataset(): void
    {
        $this->beConstructedThrough(function () {
            return new TestDataset();
        });
    }
}

class TestDataset extends Dataset
{
    public $a;
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
