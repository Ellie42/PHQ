<?php

namespace spec\PHQ\Config;

use PHQ\Config\WorkerConfig;
use PhpSpec\ObjectBehavior;
use PHQ\Exceptions\ConfigurationException;
use Prophecy\Argument;

class WorkerConfigSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerConfig::class);
    }

    function it_should_be_able_to_set_the_count_property_on_construct()
    {
        $this->beConstructedWith([
            "count" => 2
        ]);

        $this->getCount()->shouldReturn(2);
    }

    function it_should_be_able_to_determine_the_interpreter_by_file_extension_php()
    {
        $this->beConstructedWith([
            "script" => "spec/testScripts/testWorkerScript.php"
        ]);

        $this->getScriptCommand()->shouldReturn("php spec/testScripts/testWorkerScript.php");
    }

    function it_should_be_able_to_determine_the_interpreter_by_file_extension_ts()
    {
        $this->beConstructedWith([
            "script" => "spec/testScripts/testWorkerScript.ts"
        ]);

        $this->getScriptCommand()->shouldReturn("ts-node spec/testScripts/testWorkerScript.ts");
    }

    function it_should_be_able_to_determine_the_interpreter_by_file_extension_js()
    {
        $this->beConstructedWith([
            "script" => "spec/testScripts/testWorkerScript.js"
        ]);

        $this->getScriptCommand()->shouldReturn("node spec/testScripts/testWorkerScript.js");
    }

    function it_should_throw_an_error_if_type_cannot_be_determined()
    {
        $this->beConstructedWith([
            "script" => "spec/testScripts/testWorkerScript"
        ]);

        $this->shouldthrow(ConfigurationException::class)
            ->during('getScriptCommand');
    }

    function it_should_be_able_to_determine_the_interpreter_from_language_option()
    {
        $this->beConstructedWith([
            "language" => "js",
            "script" => "spec/testScripts/testWorkerScript"
        ]);

        $this->getScriptCommand()->shouldReturn("node spec/testScripts/testWorkerScript");
    }

    function it_should_use_the_command_option_if_present()
    {
        $this->beConstructedWith([
            "command" => "ts-node myscript.ts",
            "language" => "php",
            "script" => "spec/testScripts/testWorkerScript"
        ]);

        $this->getScriptCommand()->shouldReturn("ts-node myscript.ts");
    }

    function it_should_throw_an_error_if_specified_interpreter_is_not_handled(){
        $this->beConstructedWith([
            "language" => "notarealthinghopefully",
        ]);

        $this->shouldThrow(ConfigurationException::class)->during('getScriptCommand');
    }
}
