<?php

namespace spec\PHQ\Storage\MySQL;

use PDOStatement;
use PhpSpec\ObjectBehavior;
use PHQ\Data\JobDataset;
use PHQ\Exceptions\ConfigurationException;
use PHQ\Exceptions\StorageException;
use PHQ\Exceptions\StorageSetupException;
use PHQ\Jobs\Job;
use PHQ\Storage\MySQL\MySQLQueueStorage;
use Prophecy\Argument;
use spec\TestObjects\TestJob;

class MySQLQueueStorageSpec extends ObjectBehavior
{
    private $pdo;

    function let(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->beConstructedWith($pdo);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MySQLQueueStorage::class);
    }

    function it_should_be_able_to_retrieve_a_job_dataset(PDOStatement $statement)
    {
        $id = 5;

        $this->pdo->prepare(Argument::containingString(
            "SELECT `id`,`class`,`payload`, `status`, `retries`\n            FROM phq_jobs\n            WHERE id = ?")
        )->shouldBeCalled()->willReturn($statement);

        $statement->execute([(int)$id])->shouldBeCalled()->willReturn(true);

        $payload = [
            "a" => 1,
            "b" => 2,
        ];

        $statement->fetch(\PDO::FETCH_ASSOC)->shouldBeCalled()->willReturn([
            "id" => $id,
            "class" => "TestClass",
            "payload" => json_encode($payload)
        ]);

        $dataset = $this->get($id)->shouldBeAnInstanceOf(JobDataset::class);

        expect($dataset->getPayload())->shouldBe($payload);
    }

    function it_should_throw_an_error_if_unable_to_retrieve_a_job_dataset_due_to_db_error(\PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::any())->shouldBeCalled()->willReturn($statement);
        $this->pdo->errorInfo()->shouldBeCalled()->willReturn([
            null, null, "error"
        ]);
        $statement->execute(Argument::any())->shouldBeCalled()->willReturn(false);

        $this->shouldThrow()->during('get', [1]);
    }

    function it_should_serialise_a_job_and_attempt_to_save(PDOStatement $statement)
    {
        $payload = '{"test":"data"}';

        $job = new TestJob(new JobDataset([
            "payload" => [
                "test" => "data"
            ]
        ]));

        $this->pdo->prepare(Argument::containingString(
            "INSERT INTO phq_jobs (`class`, `payload`) VALUES (?, ?)")
        )->shouldBeCalled()->willReturn($statement);

        $statement->execute([
            TestJob::class,
            $payload
        ])->shouldBeCalled()->willReturn(true);

        $this->enqueue($job);
    }

    function it_should_be_able_to_return_the_next_available_job(PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::containingString(
            "SELECT `id`,`class`,`payload`,`status`, `retries`\n            FROM phq_jobs\n            WHERE status = ?"
        ))->shouldBeCalled()->willReturn($statement);

        $statement->execute(Argument::containing(Job::STATUS_IDLE))->shouldBeCalled()->willReturn(true);

        $payload = [
            "test" => "abc"
        ];

        $statement->fetch(\PDO::FETCH_ASSOC)->shouldBeCalled()->willReturn([
            "id" => 4,
            "class" => "TestClass",
            "payload" => json_encode($payload),
            "status" => 0,
            "retries" => 0
        ]);

        $dataset = $this->getNext()->shouldBeAnInstanceOf(JobDataset::class);

        expect($dataset->getPayload())->shouldBe($payload);
    }

    function it_should_return_null_if_there_are_no_more_jobs(PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::containingString(
            "SELECT `id`,`class`,`payload`,`status`, `retries`\n            FROM phq_jobs\n            WHERE status = ?"
        ))->shouldBeCalled()->willReturn($statement);

        $statement->execute(Argument::containing(Job::STATUS_IDLE))->shouldBeCalled()->willReturn(true);

        $statement->fetch(\PDO::FETCH_ASSOC)->shouldBeCalled()->willReturn(null);

        $this->getNext()->shouldReturn(null);
    }


    function it_should_throw_an_error_if_it_cannot_get_the_next_entry_due_to_db_error(PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::any())->shouldBeCalled()->willReturn($statement);

        $statement->execute(Argument::containing(Job::STATUS_IDLE))->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(\Exception::class)->during('getNext');
    }

    function it_should_create_the_required_tables_in_the_db_on_first_setup(PDOStatement $statement)
    {
        $expectedString = "
            CREATE TABLE phq_jobs(
            id INT NOT NULL AUTO_INCREMENT,
            class VARCHAR(512) NOT NULL,
            payload BLOB NOT NULL,
            status INT(3) DEFAULT 0,
            retries INT(9) DEFAULT 0,
              
            PRIMARY KEY (id)
            )
        ";
        $this->pdo->exec($expectedString)->shouldBeCalled()->willReturn($statement);

        $this->setup();
    }

    function it_should_throw_an_error_if_the_setup_fails()
    {
        $this->pdo->exec(Argument::any())->shouldBeCalled()->willReturn(false);
        $this->pdo->errorInfo()->shouldBeCalled()->willReturn([
            null,
            null,
            "Hello"
        ]);

        $this->shouldThrow(StorageSetupException::class)->during('setup');
    }

    function it_should_use_the_provided_pdo_on_init()
    {
        $this->init([]);
        $this->getPdo()->shouldReturn($this->pdo);
    }

    function it_should_setup_properties_using_specified_options()
    {
        $this->getTable()->shouldNotBe("new_table");
        $this->init([
            "table" => "new_table"
        ]);
        $this->getTable()->shouldBe("new_table");
    }

    function it_should_create_a_new_pdo_instance_using_the_passed_options()
    {
        $this->beConstructedWith(null);
        $this->shouldThrow(\PDOException::class)->during('init', [[
            "host" => "localhost",
            "user" => "noooo",
            "pass" => "hunter2",
            "port" => 1920,
            "database" => "no_db_here_today"
        ]]);
    }

    function it_should_throw_an_exception_if_required_options_are_missing()
    {
        $this->beConstructedWith(null);
        $this->shouldThrow(ConfigurationException::class)->during('init', [[
            "host" => "localhost",
            "user" => "noooo",
        ]]);
    }

    function it_should_not_be_able_to_update_a_job_without_an_id()
    {
        $this->shouldThrow(StorageException::class)->during('update', [new JobDataset()]);
    }

    function it_should_throw_a_storage_exception_when_a_pdo_error_is_thrown_during_update(\PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::any())->shouldBeCalled()->willReturn($statement);
        $this->pdo->errorInfo()->shouldBeCalled()->willReturn([null, null, "error"]);
        $statement->execute(Argument::any())->shouldBeCalled()->willThrow(\PDOException::class);
        $this->shouldThrow(StorageException::class)->during('update', [new JobDataset([
            "id" => 10
        ])]);
    }

    function it_should_be_able_to_update_a_job(\PDOStatement $statement)
    {
        $jobData = [
            "payload" => [],
            "status" => Job::STATUS_SUCCESS,
            "id" => 1
        ];

        $job = new JobDataset($jobData);

        $this->pdo->prepare("
            UPDATE phq_jobs SET class = ?, payload = ?, status = ?, retries = ?
            WHERE id = ?
        ")->shouldBeCalled()->willReturn($statement);
        $statement->execute([null, "[]", Job::STATUS_SUCCESS, null, 1])->shouldBeCalled()->willReturn(true);

        $this->update($job)->shouldReturn(true);
    }
}
