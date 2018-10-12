<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:35
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;
use PHQ\Messages\Container\JobStartMessage;
use PHQ\Messages\IMessageParser;
use PHQ\Messages\MessageParser;
use PHQ\Messages\Worker\JobFinishedMessage;
use PHQ\Messages\WorkerMessage;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class WorkerContainer implements IWorkerProcessHandler
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var IMessageParser
     */
    private $messageParser;

    private $hasJob = false;

    public function __construct(Process $process, LoopInterface $loop)
    {
        $this->process = $process;
        $this->loop = $loop;
        $this->messageParser = new MessageParser();
    }

    /**
     * Override default message parser
     * @param IMessageParser $messageParser
     */
    public function setMessageParser(IMessageParser $messageParser)
    {
        $this->messageParser = $messageParser;
    }

    /**
     * Run a new process(if not already running)
     * @param JobDataset $jobDataset
     */
    public function giveJob(JobDataset $jobDataset)
    {
        if (!$this->process->isRunning()) {
            $this->startProcess($this->loop);
        }

        $this->sendMessage(new JobStartMessage([
            "data" => $jobDataset->toArray()
        ]));

        $this->hasJob = true;
    }

    /**
     * TODO instead of Process replace with generic process adapter
     * Start the actual worker process and link up all required events
     * @param LoopInterface $loop
     */
    private function startProcess(LoopInterface $loop): void
    {
        $this->process->start($loop);

        $this->process->stdout->on('data', \Closure::fromCallable([$this, 'onData']));
        $this->process->on('exit', \Closure::fromCallable([$this, 'onExit']));
        $this->process->stdout->on('error', \Closure::fromCallable([$this, 'onError']));

        register_shutdown_function([$this, 'killProcess']);
    }

    public function killProcess()
    {
        $this->process->close();
    }

    private function sendMessage(WorkerMessage $message)
    {
        $this->process->stdin->write($message->serialise() . "\n");
    }

    public function onError(\Exception $e)
    {
    }

    public function onExit($code, $signal)
    {
    }

    /**
     * Parse any messages received from the worker process
     * @param $chunk
     * @throws \PHQ\Exceptions\PHQException
     */
    public function onData($chunk)
    {
        $data = json_decode($chunk, true);

        //Not a message, probably just regular output that should be passed through
        if($data === null){
            echo $chunk;
            return;
        }

        $message = $this->messageParser->parse($chunk);

        if($message instanceof JobFinishedMessage){
            $this->onJobFinished($message);
        }
    }

    public function hasJob(): bool
    {
        return $this->hasJob;
    }

    /**
     * On job finished we need to notify the queue manager and mark this worker as without a job
     * @param JobFinishedMessage $message
     */
    private function onJobFinished(JobFinishedMessage $message)
    {
        //TODO implement update
        $this->hasJob = false;
    }
}