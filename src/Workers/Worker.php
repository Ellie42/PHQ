<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:21
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;
use PHQ\Messages\Container\JobStartMessage;
use PHQ\Messages\IMessageParser;
use PHQ\Messages\Worker\JobFinishedMessage;
use PHQ\Messages\WorkerMessage;
use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

class Worker
{
    /**
     * Message parser can be set to allow for different serialisation techniques
     * @var IMessageParser
     */
    protected $messageParser;

    /**
     * @var WritableResourceStream
     */
    protected $stdout;

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
        $this->stdout = new WritableResourceStream(STDOUT, $loop);

        $stdin->on("data", \Closure::fromCallable([$this, 'onData']));

        $loop->run();
    }

    /**
     * Parse messages sent from the worker container
     * @param $chunk
     * @throws \PHQ\Exceptions\PHQException
     */
    public function onData($chunk)
    {
        $message = $this->messageParser->parse($chunk);

        if($message instanceof JobStartMessage){
            $this->startJob($message);
        }
    }

    /**
     * Process the message and start running the job
     * @param JobStartMessage $message
     * @throws \PHQ\Exceptions\PHQException
     */
    private function startJob(JobStartMessage $message)
    {
        $jobDataset = new JobDataset($message->data);

        $job = $jobDataset->getJob();

        $result = $job->run();

        $this->sendMessage(new JobFinishedMessage([
            "status" => $result
        ]));
    }

    /**
     * Send a message back to the workerContainer
     * @param WorkerMessage $param
     */
    private function sendMessage(WorkerMessage $param)
    {
        $this->stdout->write($param->serialise() . "\n");
    }
}