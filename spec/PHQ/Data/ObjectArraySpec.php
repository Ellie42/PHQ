<?php

namespace spec\PHQ\Data;

use PhpSpec\Exception\Example\ErrorException;
use PHQ\Data\ObjectArray;
use PhpSpec\ObjectBehavior;
use PHQ\Exceptions\TypeError;
use Prophecy\Argument;

class ObjectArraySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ObjectArray::class);
        $this->shouldHaveType(\ArrayIterator::class);
    }

    function it_should_only_allow_objects_of_class_type_if_type_is_specified()
    {
        $this->beAnInstanceOf(TestObjectArrayWithType::class);
        $this->shouldThrow(TypeError::class)->during('offsetSet', [0, new OtherTestType()]);
        $this->shouldNotThrow(TypeError::class)->during('offsetSet', [2, new TestType()]);
    }

    function it_should_only_allow_setting_of_objects(){
        $this->shouldThrow(TypeError::class)->during('offsetSet', ["a", "abc"]);
        $this->shouldThrow(TypeError::class)->during('offsetSet', ["a", 123]);
    }
}

class OtherTestType{

}

class TestType
{

}

class TestObjectArrayWithType extends ObjectArray
{
    protected $type = TestType::class;
}