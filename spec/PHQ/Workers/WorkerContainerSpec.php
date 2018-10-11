<?php

namespace spec\PHQ\Workers;

use PHQ\Data\JobDataset;
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

    /**
     * @var LoopInterface
     */
    private $loop;

    function let(Process $process, IWorkerCommunicator $communicator, LoopInterface $loop)
    {
        $this->process = $process;

        $this->beConstructedWith($process, $communicator,$loop);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerContainer::class);
    }

    function it_should_not_create_a_new_process_if_one_is_already_running()
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(true);
        $this->process->start(Argument::any())->shouldNotBeCalled();

        $this->giveJob(new JobDataset());
    }

    function it_should_start_the_new_process_and_handle_all_events_on_start(ReadableStreamInterface $stdout)
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(false);
        $this->process->start(Argument::any())->shouldBeCalled();

        $this->process->stdout = $stdout;

        $stdout->on("data", Argument::type(\Closure::class))->shouldBeCalled();
        $stdout->on("error", Argument::type(\Closure::class))->shouldBeCalled();
        $this->process->on("exit", Argument::type(\Closure::class))->shouldBeCalled();

        $this->giveJob(new JobDataset());
    }

    function it_should_indicate_that_it_has_a_job_when_given_one(){
        $this->process->isRunning()->shouldBeCalled()->willReturn(true);

        $this->giveJob(new JobDataset());

        $this->hasJob()->shouldReturn(true);
    }

    function it_should_indicate_that_is_has_no_job_when_it_doesnt(){
        $this->hasJob()->shouldReturn(false);
    }
}
