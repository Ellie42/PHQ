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
use PHQ\Data\JobDatasetArray;
use PHQ\Exceptions\AssertionException;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\StorageException;
use PHQ\Exceptions\StorageRetrievalException;
use PHQ\Exceptions\StorageSetupException;
use PHQ\Jobs\IJob;
use PHQ\Jobs\Job;
use PHQ\Storage\IQueueStorageConfigurable;
use PHQ\Storage\IQueueStorageHandler;
use PHQ\Storage\IQueueStorageNeedsSetup;
use PHQ\Traits\Assertions;

class MySQLQueueStorage implements IQueueStorageHandler, IQueueStorageConfigurable, IQueueStorageNeedsSetup
{
    use Assertions;

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

    /**
     * Perform initial setup required for this storage handler to be used
     * @throws StorageSetupException
     */
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
     * @throws StorageRetrievalException
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
            throw new StorageRetrievalException($this->pdo->errorInfo()[2]);
        }

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        return $this->createDatasetFromRow($data);
    }

    /**
     * @param IJob $job
     * @return bool
     */
    public function enqueue(IJob $job): bool
    {
        $data = json_encode($job->getData()->getPayload());

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
     * Initialise the storage handler with the config
     * @param array $options
     * @throws ConfigurationException
     */
    public function init(array $options): void
    {
        if (isset($options['table'])) {
            $this->table = $options['table'];
        }

        $this->pdo = $this->getOrCreatePdo($options);
    }

    /**
     * Creates a new instance of PDO unless one has already been set
     * @param array $options
     * @return \PDO
     * @throws ConfigurationException
     */
    private function getOrCreatePdo(array $options): \PDO
    {
        //PDO has already been provided
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        //Set default port
        if (!isset($options['port'])) {
            $options['port'] = 3306;
        }

        //Required options for the PDO instance to be created
        $requiredOptions = ["host", "port", "database", "user", "pass"];

        try {
            $this->assertKeysInArray($options, $requiredOptions);
        } catch (AssertionException $e) {
            $missingOptions = array_diff_key($options, array_flip($requiredOptions));
            $missingOptionsString = implode(', ', $missingOptions);

            throw new ConfigurationException(
                "Failed to setup storage adapter, missing required options '$missingOptionsString'"
            );
        }

        return new \PDO(
            "mysql:host={$options['host']};port={$options['port']}};dbname={$options['database']}",
            $options['user'], $options['pass']
        );
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param $data
     * @return JobDataset
     */
    private function createDatasetFromRow($data): JobDataset
    {
        if (isset($data['payload']) && strlen($data['payload']) > 0) {
            $data['payload'] = json_decode($data['payload'], true);
        }

        return new JobDataset($data);
    }

    /**
     * Update job by ID
     * @param JobDataset $jobDataset
     * @return bool
     * @throws StorageException
     */
    public function update(JobDataset $jobDataset): bool
    {
        $id = $jobDataset->getId();

        if ($id === null) {
            throw new StorageException("Cannot update a job without an id!");
        }

        $data = $jobDataset->toArray();

        $data['payload'] = $jobDataset->getSerialisedPayload();

        //Remove ID from the dataset so we don't accidentally overwrite the ID in the db
        unset($data['id']);

        $updateStrings = [];
        $updateValues = [];

        //Setup update string and values for all jobDataset properties
        foreach ($data as $key => $value) {
            $updateValues[] = $value;
            $updateStrings[] = "$key = ?";
        }

        $updateString = implode(', ', $updateStrings);

        $statement = $this->pdo->prepare("
            UPDATE {$this->table} SET $updateString
            WHERE id = ?
        ");

        $updateValues[] = $id;

        try {
            $result = $statement->execute($updateValues);
        } catch (\PDOException $e) {
            throw new StorageException("Failed to update jobdata for $id " . $this->pdo->errorInfo()[2]);
        }

        return $result;
    }

    /**
     * Return all inactive jobs
     * @param null $afterId
     * @return JobDatasetArray
     * @throws StorageException
     */
    public function getAvailable($afterId = null): JobDatasetArray
    {
        $idRestraint = "";
        $params = [
            \PHQ\Jobs\Job::STATUS_IDLE
        ];

        if($afterId !== null){
            $params[] = (int)$afterId;
            $idRestraint = "AND id > ?";
        }

        $sql = "
            SELECT `id`,`class`,`payload`, `status`, `retries`
            FROM {$this->table} WHERE status = ? $idRestraint 
            ORDER BY id 
        ";

        $statement = $this->pdo->prepare($sql);

        $result = $statement->execute($params);

        if(!$result){
            throw new StorageException("Failed to retrieve available jobs " . $this->pdo->errorInfo()[2]);
        }

        $rawJobs = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return new JobDatasetArray(array_map(function($rawJob){
            return new JobDataset($rawJob);
        },$rawJobs));
    }
}