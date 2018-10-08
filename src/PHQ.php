<?php

namespace PHQ;

use PHQ\Config\PHQConfig;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageConfigurable;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Storage\IQueueStorageNeedsSetup;
use PHQ\Workers\WorkerManager;

class PHQ
{
    /**
     * @var WorkerManager
     */
    private $workerManager = null;

    /**
     * @var IQueueStorageHandler
     */
    private $storageHandler = null;

    /**
     * @var PHQConfig
     */
    private $config;

    public function __construct(IQueueStorageHandler $storageHandler = null, PHQConfig $config = null)
    {
        //Setup the main configuration from phqconf
        if ($config === null) {
            $this->config = new PHQConfig(getcwd());
            $this->config->load();
        } else {
            $this->config = $config;
        }

        //Setup the storage handler
        if ($storageHandler === null) {
            $this->storageHandler = $this->createStorageHandler();
        } else {
            $this->storageHandler = $storageHandler;
        }

        //Setup the worker manager
        $this->workerManager = new WorkerManager($this->config->getWorkerConfig());
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

        return $this->createJobFromJobEntry($job);
    }

    /**
     * Returns an instance of IJob based on the class name in the JobDataset and sets the payload
     * @param JobDataset $jobData
     * @return IJob
     * @throws \Exception
     */
    public function createJobFromJobEntry(JobDataset $jobData): IJob
    {
        $className = $jobData->getClass();

        if (!class_exists($className)) {
            throw new PHQException("Class {$className} does not exist!");
        }

        if (!(is_subclass_of($className, IJob::class))) {
            throw new PHQException("$className is not an instance of " . IJob::class);
        }

        /**
         * @var IJob
         */
        $obj = new $className($jobData);

        return $obj;
    }

    /**
     * Creates the storage handler object from the configuration file
     * @return IQueueStorageHandler
     * @throws ConfigurationException
     */
    private function createStorageHandler(): IQueueStorageHandler
    {
        $storageConfig = $this->config->getStorageConfig();

        if ($storageConfig === null) {
            throw new ConfigurationException("No storage handler has been specified!");
        }

        return $storageConfig->getStorage();
    }

    /**
     * Perform initial setup required for PHQ to run on a new application
     */
    public function setup()
    {
        if ($this->storageHandler instanceof IQueueStorageNeedsSetup) {
            $this->storageHandler->setup();
        }
    }

    /**
     * Update a job
     * @param Job $job
     * @return bool
     */
    public function update(Job $job): bool
    {
        return $this->storageHandler->update($job->getData());
    }

    /**
     * Grab the next job, run it and then update status
     */
    public function process()
    {
        $job = $this->getNext();

        $status = $job->run();

        //Set the new status on the job's dataset
        $job->getData()->setStatus($status);

        $this->update($job);
    }

    public function start() : bool
    {
        return true;
    }
}