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
     * WorkerManager constructor.
     * @param WorkerConfig $config
     * @param PHQ $phq
     */
    public function __construct(WorkerConfig $config, PHQ $phq)
    {
        $this->config = $config;
        $this->phq = $phq;

        $this->workers = new WorkerContainerArray();

        for ($i = 0; $i < $this->config->count; $i++) {
            $worker = new WorkerContainer(new Process($config->getScriptCommand()));

            $this->workers[] = $worker;
        }
    }

    /**
     * Instantiate all worker containers and start sending jobs if possible
     */
    public function startWorking(): void
    {
        $this->assignJobs();
    }

    public function getWorkerContainers(): WorkerContainerArray
    {
        return $this->workers;
    }

    /**
     * Assign a job to all free workers
     * TODO Implement
     */
    private function assignJobs()
    {
        foreach($this->workers as $worker){
        }
    }
}