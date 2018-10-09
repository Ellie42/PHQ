<?php

namespace spec\PHQ\EventBus;

use PHQ\Config\EventBusConfig;
use PHQ\EventBus\PeriodicEventBus;
use PhpSpec\ObjectBehavior;
use PHQ\Jobs\IJobEventListener;
use Prophecy\Argument;
use React\EventLoop\LoopInterface;
use spec\PHQ\EventBusConfigMock;

class PeriodicEventBusSpec extends ObjectBehavior
{
    /**
     * @var IJobEventListener
     */
    protected $eventListener;

    /**
     * @var EventBusConfigMock
     */
    protected $config;

    function let(IJobEventListener $eventListener, EventBusConfigMock $config)
    {
        $this->eventListener = $eventListener;
        $this->config = $config;
        $this->beConstructedWith($eventListener, $config);
    }

    function it_is_initializable()
    {
        $this->config->getOptions()->shouldBeCalled()->willReturn([]);
        $this->shouldHaveType(PeriodicEventBus::class);
    }

    function it_should_trigger_reassignment_of_jobs_by_an_interval(LoopInterface $loop)
    {
        $this->config->getOptions()->shouldBeCalled()->willReturn([
            "interval" => 1000
        ]);
        $loop->addPeriodicTimer(1000, Argument::type(\Closure::class))
            ->shouldBeCalled();
        $this->start($loop);
    }
}
