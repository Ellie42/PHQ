<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 07/10/2018
 * Time: 10:10
 */

namespace PHQ\Workers;


use PHQ\Config\WorkerConfig;
use PHQ\PHQ;
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class WorkerManager
{
    /**
     * @var WorkerConfig
     */
    private $config;

    /**
     * @var WorkerContainerArray | WorkerContainer[]
     */
    private $workers;

    /**
     * @var PHQ
     */
    private $phq;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var \Closure
     */
    private $workerContainerFactory;

    /**
     * WorkerManager constructor.
     * @param WorkerConfig $config
     * @param PHQ $phq
     * @param LoopInterface|null $loop
     */
    public function __construct(WorkerConfig $config, PHQ $phq, LoopInterface $loop = null)
    {
        $this->config = $config;
        $this->phq = $phq;

        if ($loop === null) {
            $loop = Factory::create();
        }

        $this->loop = $loop;
    }

    /**
     * Set a replacement factory closure to create a WorkerContainer
     * @param \Closure $factory
     */
    public function setWorkerContainerFactory(\Closure $factory)
    {
        $this->workerContainerFactory = $factory;
    }

    /**
     * @return \Closure
     */
    public function getWorkerContainerFactory()
    {
        return $this->workerContainerFactory;
    }

    /**
     * Instantiate all worker containers and start sending jobs if possible
     */
    public function startWorking(): void
    {
        $this->workers = new WorkerContainerArray();

        for ($i = 0; $i < $this->config->count; $i++) {
            $worker = $this->createWorkerContainerInstance();

            $this->workers[] = $worker;

            $worker->start($this->loop);
        }

        $this->loop->run();
    }

    public function getWorkerContainers(): WorkerContainerArray
    {
        return $this->workers;
    }

    /**
     * @return WorkerContainer
     * @throws \PHQ\Exceptions\ConfigurationException
     */
    private function createWorkerContainerInstance(): WorkerContainer
    {
        if ($this->workerContainerFactory === null) {
            return new WorkerContainer(new Process($this->config->getScriptCommand()));
        }

        return call_user_func($this->workerContainerFactory,new Process($this->config->getScriptCommand()));
    }
}