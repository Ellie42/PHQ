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

class WorkerContainer
{
    /**
     * @var Process
     */
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function start()
    {
        if(!$this->process->isRunning()){
            $this->startProcess();
        }
    }

    private function startProcess(): void
    {
        $loop = Factory::create();

        $this->process->start($loop);

        $this->process->on('data', function ($chunk) {
            var_dump($chunk);
        });

        $this->process->on('end', [$this, 'onEnd']);

        $loop->run();
    }

    private function onEnd($code, $signal){
        var_dump("Worker process closed :(");
    }
}