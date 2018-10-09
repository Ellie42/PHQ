<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:35
 */

namespace PHQ\Workers;


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

    public function __construct(Process $process, IWorkerCommunicator $communicator)
    {
        $this->process = $process;
        $this->communicator = $communicator;
    }

    /**
     * Run a new process(if not already running)
     * @param LoopInterface $loop
     */
    public function start(LoopInterface $loop)
    {
        if (!$this->process->isRunning()) {
            $this->startProcess($loop);
        }
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
}