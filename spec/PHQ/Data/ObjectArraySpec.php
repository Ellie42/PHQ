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
    }

    function it_should_allow_setting_and_getting_of_values()
    {
        $value = new TestType();

        $this->offsetSet(0, $value);
        $this->offsetGet(0)->shouldBe($value);

        $this->offsetSet(1155142, $value);
        $this->offsetGet(1155142)->shouldBe($value);

        $this->offsetSet(-1155142, $value);
        $this->offsetGet(-1155142)->shouldBe($value);

        $this->offsetSet("abc", $value);
        $this->offsetGet("abc")->shouldBe($value);
    }

    function it_should_throw_an_error_if_getting_non_existent_value()
    {
        $this->shouldThrow(ErrorException::class)->during("offsetGet", [11]);
    }

    function it_should_be_return_whether_an_offset_exists_or_not()
    {
        $this->offsetSet(57, new TestType());
        $this->offsetSet(-194, new TestType());
        $this->offsetSet("keykey", new TestType());

        $this->offsetExists(1)->shouldReturn(false);
        $this->offsetExists(57)->shouldReturn(true);

        $this->offsetExists("keykey")->shouldReturn(true);
        $this->offsetExists("nokey")->shouldReturn(false);

        $this->offsetExists(-194)->shouldReturn(true);
        $this->offsetExists(-1481924)->shouldReturn(false);
    }

    function it_should_be_able_to_unset_an_entry()
    {
        $this->offsetSet(57, new TestType());
        $this->offsetSet(-194, new TestType());
        $this->offsetSet("keykey", new TestType());

        $this->offsetUnset(57);
        $this->offsetUnset(-194);
        $this->offsetUnset("keykey");

        $this->offsetExists(57)->shouldBe(false);
        $this->offsetExists(-194)->shouldBe(false);
        $this->offsetExists("keykey")->shouldBe(false);
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