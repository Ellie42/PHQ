<?php

namespace spec\PHQ\Messages;

use PHQ\Exceptions\PHQException;
use PHQ\Messages\MessageParser;
use PhpSpec\ObjectBehavior;
use PHQ\Messages\WorkerMessage;
use Prophecy\Argument;

class MessageParserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MessageParser::class);
    }

    function it_should_be_able_to_parse_a_json_message()
    {
        $message = json_encode([
            "type" => TestMessage::class
        ]);

        $this->parse($message)->shouldBeAnInstanceOf(TestMessage::class);
    }

    function it_should_throw_an_error_if_the_message_is_not_valid_json()
    {
        $message = "abc";

        $this->shouldThrow(PHQException::class)->during('parse', [$message]);
    }

    function it_should_throw_an_error_if_the_message_type_is_not_sent()
    {
        $message = json_encode([
            "type" => NotAGoodMessage::class
        ]);

        $this->shouldThrow(PHQException::class)->during('parse', [$message]);
    }
}

class NotAGoodMessage
{

}


class TestMessage extends WorkerMessage
{

}