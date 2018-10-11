<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:35
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;
use PHQ\Messages\JobStartMessage;
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

    private $hasJob = false;

    public function __construct(Process $process, LoopInterface $loop)
    {
        $this->process = $process;
        $this->loop = $loop;
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

        $this->sendMessage(new JobStartMessage($jobDataset->toArray()));

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
    }

    private function sendMessage(WorkerMessage $message)
    {
        $this->process->stdin->write($message->serialise() . '\n');
    }

    public function onError(\Exception $e)
    {
    }

    public function onExit($code, $signal)
    {
    }

    public function onData($chunk)
    {
    }

    public function hasJob(): bool
    {
        return $this->hasJob;
    }
}