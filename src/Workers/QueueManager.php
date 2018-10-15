<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 07/10/2018
 * Time: 10:10
 */

namespace PHQ\Workers;


use PHQ\Config\WorkerConfig;
use PHQ\Data\JobDataset;
use PHQ\PHQ;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class QueueManager
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
     * @var \SplQueue
     */
    private $queue;

    /**
     * @var int
     */
    private $lastJobId = 0;

    /**
     * WorkerManager constructor.
     * @param WorkerConfig $config
     * @param PHQ $phq
     */
    public function __construct(
        WorkerConfig $config,
        PHQ $phq
    )
    {
        $this->config = $config;
        $this->phq = $phq;

        $this->queue = new \SplQueue();
        $this->workers = new WorkerContainerArray();
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
        $this->initialiseWorkers($loop);

        $this->assignNewJobs();

        $loop->addPeriodicTimer(0, function () {});

        $loop->run();
    }

    /**
     * @return WorkerContainerArray
     */
    public function getWorkerContainers(): WorkerContainerArray
    {
        return $this->workers;
    }

    /**
     * Creates a worker container instance either directly or using a configured factory
     * @param LoopInterface $loop
     * @return WorkerContainer
     * @throws \PHQ\Exceptions\ConfigurationException
     */
    private function createWorkerContainerInstance(LoopInterface $loop): WorkerContainer
    {
        if ($this->workerContainerFactory === null) {
            return new WorkerContainer(new Process($this->config->getScriptCommand()), $loop);
        }

        return call_user_func($this->workerContainerFactory, new Process($this->config->getScriptCommand()), $loop);
    }

    /**
     * A new job has been added, assign the newest job to a worker if possible
     */
    public function assignNewJobs()
    {
        $jobsAreAvailable = $this->ensureJobsAvailable();

        if(!$jobsAreAvailable){
            return;
        }

        //Only attempt to assign jobs to workers without a job currently
        foreach ($this->getFreeWorkers() as $worker) {
            $nextJob = $this->getNextJob();

            //No more jobs remaining
            if (!$nextJob) {
                return;
            }

            $worker->giveJob($nextJob);
        }
    }

    /**
     * Return array of all workers currently without a job
     * @return array|WorkerContainer[]|WorkerContainerArray
     */
    private function getFreeWorkers()
    {
        return array_filter(iterator_to_array($this->workers), function (WorkerContainer $worker) {
            return !$worker->hasJob();
        });
    }

    /**
     * Return next available job from the local queue
     * @return mixed
     */
    private function getNextJob(): ?JobDataset
    {
        if($this->queue->isEmpty()){
            return null;
        }

        return $this->queue->dequeue();
    }

    /**
     * Update the local job queue from the storage handler
     */
    private function updateJobQueue(): void
    {
        $jobs = $this->phq->getStorageHandler()->getAvailable($this->lastJobId);

        $jobCount = count($jobs);

        //No new jobs, return
        if ($jobCount <= 0) {
            return;
        }

        //Last job id is used as the pivot for the next job retrieval
        $this->lastJobId = $jobs[$jobCount - 1];

        //Add to local queue
        foreach ($jobs as $job) {
            $this->queue->push($job);
        }
    }

    /**
     * Create worker instances
     * @param LoopInterface $loop
     * @throws \PHQ\Exceptions\ConfigurationException
     */
    private function initialiseWorkers(LoopInterface $loop): void
    {
        $this->workers = new WorkerContainerArray();

        for ($i = 0; $i < $this->config->getCount(); $i++) {
            $worker = $this->createWorkerContainerInstance($loop);

            $this->workers[] = $worker;
        }
    }

    /**
     * Make sure that jobs are available in the local queue if at all possible
     * @return bool
     */
    private function ensureJobsAvailable() : bool
    {
        //Check existing jobs in queue, if there are none then try to get updated jobs from the storage handler
        if ($this->queue->isEmpty()) {
            $this->updateJobQueue();

            //if still nothing then return
            if ($this->queue->isEmpty()) {
                return false;
            }
        }

        return true;
    }
}