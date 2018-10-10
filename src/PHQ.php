<?php

namespace PHQ;

use PHQ\Config\PHQConfig;
use PHQ\Data\JobDataset;
use PHQ\EventBus\PeriodicEventBus;
use PHQ\EventBus\IJobEventBus;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\PHQException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\IJobEventListener;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Storage\IQueueStorageNeedsSetup;
use PHQ\Workers\WorkerManager;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class PHQ implements IJobEventListener
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

    /**
     * @var IJobEventBus
     */
    private $jobEventBus;

    /**
     * @var LoopInterface
     */
    private $eventLoop;

    /**
     * This constructor has gotten ridiculous,
     * TODO replace with either setters for each param or implement builder pattern
     * PHQ constructor.
     * @param IQueueStorageHandler|null $storageHandler
     * @param PHQConfig|null $config
     * @param WorkerManager|null $workerManager
     * @param IJobEventBus|null $jobEventBus
     * @param LoopInterface|null $loop
     * @throws ConfigurationException
     */
    public function __construct(
        IQueueStorageHandler $storageHandler = null,
        PHQConfig $config = null,
        WorkerManager $workerManager = null,
        IJobEventBus $jobEventBus = null,
        LoopInterface $loop = null
    )
    {
        if($loop === null){
            $this->eventLoop = Factory::create();
        }else{
            $this->eventLoop = $loop;
        }

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
        if ($workerManager === null) {
            $this->workerManager = new WorkerManager($this->config->getWorkerConfig(), $this);
        } else {
            $this->workerManager = $workerManager;
        }

        $this->setupEventBus($jobEventBus);
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

    /**
     * Start processing jobs
     * This is a blocking call that will continue forever or until an error is uncaught
     */
    public function start()
    {
        $this->jobEventBus->start($this->eventLoop);
        $this->workerManager->startWorking($this->eventLoop);
    }

    /**
     * Assign the new job to a worker
     * @param int|null $id
     */
    public function onJobAdded(?int $id = null)
    {
        if ($id === null) {
            $this->workerManager->assignNewJobs();
        } else {
            $this->workerManager->assignJobById($id);
        }
    }

    /**
     * Configure and assign the event bus
     * @param IJobEventBus $jobEventBus
     * @throws ConfigurationException
     */
    private function setupEventBus(?IJobEventBus $jobEventBus): void
    {
        //Setup the event receiver which should handle new job add/update events and trigger the worker manager
        //to assign new jobs
        if ($jobEventBus === null) {
            $eventBusConfig = $this->config->getEventBusConfig();
            $eventBusClass = $eventBusConfig->getClass();

            if (!is_subclass_of($eventBusClass, IJobEventBus::class)) {
                throw new ConfigurationException("Specified event bus $eventBusClass does not implement " . IJobEventBus::class);
            }

            $this->jobEventBus = new $eventBusClass($this, $this->config->getEventBusConfig());
        } else {
            $this->jobEventBus = $jobEventBus;
        }
    }

    /**
     * Force update of jobs
     * @return mixed
     */
    public function updateJobs()
    {
        $this->workerManager->assignNewJobs();
    }
}