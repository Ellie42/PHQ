<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:21
 */

namespace PHQ\Workers;


use PHQ\Messages\IMessageParser;
use PHQ\Messages\JobStartMessage;
use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;

class Worker
{
    protected $messageParser;

    public function __construct(IMessageParser $messageParser)
    {
        $this->messageParser = $messageParser;
    }

    /**
     * Start the worker process, will listen for new messages and carry out jobs when requested.
     */
    public function start()
    {
        $loop = Factory::create();

        $stdin = new ReadableResourceStream(STDIN, $loop);

        $stdin->on("data", \Closure::fromCallable([$this, 'chunk']));

        $loop->run();
    }

    public function onData($chunk)
    {
        $message = $this->messageParser->parse($chunk);


        //Need job dataset parser
        if($message instanceof JobStartMessage){
//            $this->startJob($message->);
        }
    }

    private function startJob()
    {
    }
}