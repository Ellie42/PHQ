<?php

namespace PHQ;

use PHQ\Config\PHQConfig;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageHandler;

class PHQ
{
    /**
     * @var IQueueStorageHandler
     */
    private $storageHandler;

    /**
     * @var PHQConfig
     */
    private $config;

    public function __construct(IQueueStorageHandler $storageHandler = null, PHQConfig $config = null)
    {
        if ($config === null) {
            $this->config = new PHQConfig(getcwd());
            $this->config->load();
        }else{
            $this->config = $config;
        }

        $this->setupStorageHandler($storageHandler);
    }

    public function getStorageHandler(): IQueueStorageHandler
    {
        return $this->storageHandler;
    }

    /**
     * @param IJob $job
     */
    public function enqueue(IJob $job)
    {
        $this->storageHandler->enqueue($job);
    }

    /**
     * Return the next job from the queue
     * @return IJob
     * @throws \Exception
     */
    public function getNext(): IJob
    {
        $job = $this->storageHandler->getNext();

        return Job::fromJobEntry($job);
    }

    /**
     * @param IQueueStorageHandler $storageHandler
     * @throws Exceptions\ConfigurationException
     */
    private function setupStorageHandler(?IQueueStorageHandler $storageHandler): void
    {
        if ($storageHandler === null) {
            $storageConfig = $this->config->getStorageConfig();

            if ($storageConfig === null) {
                throw new ConfigurationException("No storage handler has been specified!");
            }

            $this->storageHandler = $storageConfig->getStorage();
        } else {
            $this->storageHandler = $storageHandler;
        }
    }
}