<?php

namespace spec\PHQ\Workers;

use PHQ\Workers\WorkerContainer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use React\ChildProcess\Process;

class WorkerContainerSpec extends ObjectBehavior
{
    /**
     * @var Process
     */
    private $process;

    function let(Process $process){
        $this->process = $process;

        $this->beConstructedWith($process);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WorkerContainer::class);
    }

    function it_should_be_able_to_spawn_a_worker_process(){

    }
}
