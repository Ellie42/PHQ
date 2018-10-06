<?php

namespace spec\PHQ\Traits;

use PHQ\Exceptions\AssertionException;
use PHQ\Traits\Assertions;
use PhpSpec\ObjectBehavior;

class AssertionsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf(AssertionsTest::class);
    }

    function it_should_assert_keys_in_array_exist()
    {
        $this->shouldNotThrow(AssertionException::class)
            ->during('assertKeysInArray_test', [
                ["a" => 1, "b" => 2, "c" => 3],
                ["a", "b", "c"]
            ]);
    }

    /**
     * Writing unit test names in BDD framework is hard :(
     */
    function it_should_assert_keys_in_array_exist_and_throw_exception_when_it_doesnt()
    {
        $this->shouldThrow(AssertionException::class)
            ->during("assertKeysInArray_test", [
                ["a" => 1, "b" => 2],
                ["a", "c"]
            ]);

        $this->shouldThrow(AssertionException::class)
            ->during("assertKeysInArray_test", [
                [],
                ["a", "c"]
            ]);
    }
}


class AssertionsTest
{
    use Assertions;

    public function assertKeysInArray_test(array $array, array $keys)
    {
        $this->assertKeysInArray($array, $keys);
    }
}