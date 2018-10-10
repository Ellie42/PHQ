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
     * @var \Closure
     */
    private $workerContainerFactory;

    /**
     * @var IWorkerCommunicator
     */
    private $communicator;

    /**
     * WorkerManager constructor.
     * @param WorkerConfig $config
     * @param PHQ $phq
     * @param IWorkerCommunicator|null $communicator
     */
    public function __construct(
        WorkerConfig $config,
        PHQ $phq,
        IWorkerCommunicator $communicator = null
    )
    {
        $this->config = $config;
        $this->phq = $phq;

        $this->workers = new WorkerContainerArray();

        if($communicator === null){
            $this->communicator = new WorkerCommunicationAdapter();
        }else{
            $this->communicator = $communicator;
        }
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
     * @param LoopInterface $loop
     * @throws \PHQ\Exceptions\ConfigurationException
     */
    public function startWorking(LoopInterface $loop): void
    {
        $this->workers = new WorkerContainerArray();

        for ($i = 0; $i < $this->config->count; $i++) {
            $worker = $this->createWorkerContainerInstance();

            $this->workers[] = $worker;

            $worker->start($loop);
        }

        $loop->addPeriodicTimer(0, function(){});

        $loop->run();
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
            return new WorkerContainer(new Process($this->config->getScriptCommand()), $this->communicator);
        }

        return call_user_func($this->workerContainerFactory, new Process($this->config->getScriptCommand()));
    }

    /**
     * Assign a job to a worker by id if possible
     * @param int $id
     */
    public function assignJobById(int $id)
    {
        throw new \Exception("not implemented");
    }

    /**
     * A new job has been added, assign the newest job to a worker if possible
     */
    public function assignNewJobs()
    {
        throw new \Exception("not implemented");
    }
}