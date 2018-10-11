<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:35
 */

namespace PHQ\Workers;


use PHQ\Data\JobDataset;
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class WorkerContainer implements IWorkerProcessHandler
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var IWorkerCommunicator
     */
    private $communicator;

    /**
     * @var LoopInterface
     */
    private $loop;

    private $hasJob = false;

    public function __construct(Process $process, IWorkerCommunicator $communicator, LoopInterface $loop)
    {
        $this->process = $process;
        $this->communicator = $communicator;
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

        $this->hasJob = true;
    }

    /**
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

    public function onError(\Exception $e)
    {
    }

    public function onExit($code, $signal)
    {
    }

    public function onData($chunk)
    {
    }

    public function hasJob() : bool
    {
        return $this->hasJob;
    }
}