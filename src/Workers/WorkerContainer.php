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

    public function __construct(Process $process)
    {
        $this->process = $process;
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

    private function startProcess(LoopInterface $loop): void
    {
        $this->process->start($loop);

        $this->process->stdout->on('data', \Closure::fromCallable([$this, 'onData']));
        $this->process->on('exit', \Closure::fromCallable([$this, 'onExit']));
        $this->process->stdout->on('error', \Closure::fromCallable([$this, 'onError']));
    }

    public function onError(\Exception $e)
    {
        var_dump($e);
    }

    public function onExit($code, $signal)
    {
        var_dump("Worker process closed :(");
    }

    public function onData($chunk)
    {
        var_dump($chunk);
    }
}