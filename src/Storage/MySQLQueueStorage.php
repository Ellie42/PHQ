<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 11:20
 */

namespace PHQ\Storage;


use PHPUnit\Runner\Exception;
use PHQ\Data\JobDataset;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;

class MySQLQueueStorage implements IQueueStorageHandler
{
    /**
     * @var \PDO
     */
    protected $pdo;
    /**
     * @var string
     */
    protected $table;

    public function __construct(\PDO $pdo, string $table = 'phq_jobs')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * @param $id
     * @return JobDataset
     */
    function get($id): JobDataset
    {
        $statement = $this->pdo->prepare("
            SELECT `id`,`class`,`payload`, `status`, `retries`
            FROM {$this->table}
            WHERE id = ?            
        ");

        $result = $statement->execute([(int)$id]);

        if (!$result) {
            throw new Exception($this->pdo->errorInfo(), $this->pdo->errorCode());
        }

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        return new JobDataset($data);
    }

    /**
     * @param IJob $job
     * @return bool
     */
    public function enqueue(IJob $job): bool
    {
        $data = $job->serialise();

        $statement = $this->pdo->prepare("
            INSERT INTO {$this->table} (`class`, `payload`) VALUES (?, ?)
        ");

        $result = $statement->execute([
            get_class($job),
            $data
        ]);

        return $result;
    }

    /**
     * Get next job in queue
     * @return JobDataset | null
     * @throws \Exception
     */
    public function getNext(): ?JobDataset
    {
        $statement = $this->pdo->prepare("
            SELECT `id`,`class`,`payload`,`status`, `retries`
            FROM {$this->table}
            WHERE status = ?
            ORDER BY id ASC
        ");

        $result = $statement->execute([Job::STATUS_IDLE]);

        if(!$result){
            throw new \Exception($this->pdo->errorInfo(), $this->pdo->errorCode());
        }

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        if($data === null){
            return null;
        }

        return new JobDataset($data);
    }
}