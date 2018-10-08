<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 07/10/2018
 * Time: 10:10
 */

namespace PHQ\Workers;


use PHQ\Config\WorkerConfig;

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

    public function __construct(WorkerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Instantiate all worker containers and start sending jobs if possible
     */
    public function startWorking(): void
    {
        $this->workers = new WorkerContainerArray();

        for ($i = 0; $i < $this->config->workerCount; $i++) {
            $worker = new WorkerContainer();

            $this->workers[] = $worker;
        }
    }

    public function getWorkerContainers(): WorkerContainerArray
    {
        return $this->workers;
    }
}