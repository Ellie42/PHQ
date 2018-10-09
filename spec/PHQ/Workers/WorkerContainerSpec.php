<?php

namespace spec\PHQ\Workers;

use PHQ\Workers\IWorkerCommunicator;
use PHQ\Workers\WorkerContainer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableStreamInterface;

class WorkerContainerSpec extends ObjectBehavior
{
    /**
     * @var Process
     */
    private $process;

    function let(Process $process, IWorkerCommunicator $communicator)
    {
        $this->process = $process;

        $this->beConstructedWith($process, $communicator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerContainer::class);
    }

    function it_should_not_create_a_new_process_if_one_is_already_running(LoopInterface $loop)
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(true);
        $this->process->start(Argument::any())->shouldNotBeCalled();

        $this->start($loop);
    }

    function it_should_start_the_new_process_and_handle_all_events_on_start(LoopInterface $loop, ReadableStreamInterface $stdout)
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(false);
        $this->process->start(Argument::any())->shouldBeCalled();

        $this->process->stdout = $stdout;

        $stdout->on("data", Argument::type(\Closure::class))->shouldBeCalled();
        $stdout->on("error", Argument::type(\Closure::class))->shouldBeCalled();
        $this->process->on("exit", Argument::type(\Closure::class))->shouldBeCalled();

        $this->start($loop);
    }
}
