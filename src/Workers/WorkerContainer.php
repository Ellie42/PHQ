<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:35
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Messages\Container\JobStartMessage;
use PHQ\Messages\IMessageParser;
use PHQ\Messages\MessageParser;
use PHQ\Messages\Worker\JobFinishedMessage;
use PHQ\Messages\WorkerMessage;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Stream\WritableResourceStream;
use React\Stream\WritableStreamInterface;

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

    /**
     * @var WritableStreamInterface
     */
    private $stderr;

    /**
     * @var IWorkerEventHandler
     */
    private $workerEventHandler;

    private $hasJob = false;

    /**
     * @var JobDataset
     */
    private $currentJobDataset;

    /**
     * WorkerContainer constructor.
     * @param Process $process
     * @param LoopInterface $loop
     */
    public function __construct(Process $process, LoopInterface $loop)
    {
        $this->process = $process;
        $this->loop = $loop;
        $this->messageParser = new MessageParser();
        $this->stderr = new WritableResourceStream(STDERR, $loop);
    }

    public function setWorkerEventHandler(IWorkerEventHandler $workerEventHandler): void
    {
        $this->workerEventHandler = $workerEventHandler;
    }

    public function getWorkerEventHandler(): IWorkerEventHandler
    {
        return $this->workerEventHandler;
    }

    /**
     * @param WritableStreamInterface $stderr
     */
    public function setStderr(WritableStreamInterface $stderr)
    {
        $this->stderr = $stderr;
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
     * @return IMessageParser|MessageParser
     */
    public function getMessageParser()
    {
        return $this->messageParser;
    }

    /**
     * Run a new process(if not already running)
     * @param JobDataset $jobDataset
     */
    public function giveJob(JobDataset $jobDataset)
    {
        $this->currentJobDataset = $jobDataset;

        if (!$this->process->isRunning()) {
            $this->startProcess($this->loop);
        }

        //Send the job data to the worker process
        $this->sendMessage(new JobStartMessage([
            "data" => $jobDataset->toArray()
        ]));
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

        //On script end or terminate kill the child process if it is running
        register_shutdown_function([$this, 'killProcess']);
    }

    /**
     * Terminate child process
     */
    public function killProcess()
    {
        if ($this->process->isRunning()) {
            $this->process->close();
        }
    }

    private function sendMessage(WorkerMessage $message)
    {
        $this->process->stdin->write($message->serialise() . "\n\n");
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
     */
    public function onData($chunk)
    {
        $data = json_decode($chunk, true);

        //Not a message, probably just regular output that should be passed through
        if ($data === null) {
            echo $chunk;
            return;
        }

        try {
            $message = $this->messageParser->parse($chunk);
        } catch (PHQException $e) {
            $this->stderr->write("Failed to parse message: {$e->getMessage()}\n");
            return;
        }

        if ($message instanceof JobFinishedMessage) {
            $this->currentJobDataset->status = $message->status;

            if ($this->workerEventHandler) {
                $this->workerEventHandler->onJobCompleted($this, $message);
            }

            $this->currentJobDataset = null;
            return;
        }

        $this->stderr->write("Unhandled message {$message->type}\n");
    }

    public function hasJob(): bool
    {
        return $this->currentJobDataset !== null;
    }

    function getCurrentJob(): JobDataset
    {
        return $this->currentJobDataset;
    }
}