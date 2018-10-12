<?php

namespace spec\PHQ\Workers;

use PHQ\Data\JobDataset;
use PHQ\Messages\IMessageParser;
use PHQ\Messages\Worker\JobFinishedMessage;
use PHQ\Workers\IWorkerCommunicator;
use PHQ\Workers\WorkerContainer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableResourceStream;
use React\Stream\WritableStreamInterface;
use spec\TestObjects\TestJob;

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

    function let(Process $process, LoopInterface $loop)
    {
        $this->process = $process;

        $this->beConstructedWith($process, $loop);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerContainer::class);
    }

    function it_should_not_create_a_new_process_if_one_is_already_running(WritableStreamInterface $stdin)
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(true);
        $this->process->start(Argument::any())->shouldNotBeCalled();

        $this->process->stdin = $stdin;

        $stdin->write(Argument::any())->shouldBeCalled();

        $this->giveJob(new JobDataset());
    }

    function it_should_start_the_new_process_and_handle_all_events_on_start(ReadableStreamInterface $stdout, WritableStreamInterface $stdin)
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(false);
        $this->process->start(Argument::any())->shouldBeCalled();

        $this->process->stdout = $stdout;
        $this->process->stdin = $stdin;

        $stdin->write(Argument::any())->shouldBeCalled();
        $stdout->on("data", Argument::type(\Closure::class))->shouldBeCalled();
        $stdout->on("error", Argument::type(\Closure::class))->shouldBeCalled();
        $this->process->on("exit", Argument::type(\Closure::class))->shouldBeCalled();

        $this->giveJob(new JobDataset());
    }

    function it_should_indicate_that_it_has_a_job_when_given_one(WritableStreamInterface $stdin)
    {
        $this->mockGiveJob($stdin);
    }

    function it_should_indicate_that_is_has_no_job_when_it_doesnt()
    {
        $this->hasJob()->shouldReturn(false);
    }

    function it_should_be_able_to_kill_the_child_process()
    {
        $this->process->isRunning()->shouldBeCalled()->willReturn(true);
        $this->process->close()->shouldBeCalled();
        $this->killProcess();
    }

    function it_should_allow_you_to_override_the_message_parser(IMessageParser $messageParser){
        $this->setMessageParser($messageParser);
        $this->getMessageParser()->shouldReturn($messageParser);
    }

    function it_should_update_the_worker_status_and_send_notice_when_job_is_finished(WritableStreamInterface $stdin){
        $this->mockGiveJob($stdin);

        $message = new JobFinishedMessage();
        $this->onData($message->serialise());

        $this->hasJob()->shouldReturn(false);
    }

    function it_should_output_sent_data_if_it_is_not_a_message(WritableStreamInterface $stdin){
        $this->mockGiveJob($stdin);

        ob_start();
        $this->onData("abc");
        $echo = ob_get_clean();

        expect($echo)->shouldBe("abc");
    }

    function it_should_handle_all_exceptions_by_writing_to_stderr(){

    }

    /**
     * @param WritableStreamInterface $stdin
     */
    private function mockGiveJob(WritableStreamInterface $stdin): void
    {
        $this->process->isRunning()->willReturn(true);
        $this->process->stdin = $stdin;
        $stdin->write(Argument::any())->shouldBeCalled();
        $this->giveJob(new JobDataset());
        $this->hasJob()->shouldReturn(true);
    }
}


