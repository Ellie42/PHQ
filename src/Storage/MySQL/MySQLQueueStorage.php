<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 04/10/2018
 * Time: 11:20
 */

namespace PHQ\Storage\MySQL;


use Phinx\Console\PhinxApplication;
use PHPUnit\Runner\Exception;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\StorageSetupException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageConfigurable;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Storage\IQueueStorageNeedsSetup;

class MySQLQueueStorage implements IQueueStorageHandler, IQueueStorageConfigurable, IQueueStorageNeedsSetup
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $table;

    public function __construct(\PDO $pdo = null, string $table = 'phq_jobs')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function setup(): void
    {
        $result = $this->pdo->exec("
            CREATE TABLE phq_jobs(
              id INT NOT NULL AUTO_INCREMENT,
              class VARCHAR(512) NOT NULL,
              payload BLOB NOT NULL,
              status INT(3) DEFAULT 0,
              retries INT(9) DEFAULT 0,
              
              PRIMARY KEY (id)
            )
        ");

        if ($result === false) {
            throw new StorageSetupException(
                "Failed to setup DB storage " . $this->pdo->errorInfo()[2]
            );
        }
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
            throw new Exception($this->pdo->errorInfo()[2]);
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
        $sql = "
            SELECT `id`,`class`,`payload`,`status`, `retries`
            FROM {$this->table}
            WHERE status = ?
            ORDER BY id ASC
        ";

        $statement = $this->pdo->prepare($sql);

        $result = $statement->execute([Job::STATUS_IDLE]);

        if (!$result) {
            throw new \Exception($this->pdo->errorInfo()[2]);
        }

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($data === null) {
            return null;
        }

        return new JobDataset($data);
    }

    /**
     * Initialise the storage handler with the config
     * @param array $options
     */
    public function init(array $options): void
    {
        $this->pdo = $this->getOrCreatePdo($options);
    }

    /**
     * Creates a new instance of PDO unless one has already been set
     * @param array $options
     * @return \PDO
     */
    private function getOrCreatePdo(array $options): \PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        return new \PDO(
            "mysql:host={$options['host']};port={$options['port']}};dbname={$options['database']}",
            $options['user'], $options['pass']
        );
    }
}