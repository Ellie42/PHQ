<?php

namespace spec\PHQ\Logging;

use PHQ\Logging\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoggerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Logger::class);
    }
}
