<?php

namespace spec\PHQ\Storage;

use PDOStatement;
use PHQ\Data\JobDataset;
use PHQ\Jobs\Job;
use PHQ\Storage\MySQLQueueStorage;
use PhpSpec\ObjectBehavior;
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

        $statement->fetch(\PDO::FETCH_ASSOC)->shouldBeCalled()->willReturn([
            "id" => $id,
            "class" => "TestClass",
            "payload" => "abc"
        ]);

        $this->get($id);
    }

    function it_should_serialise_a_job_and_attempt_to_save(TestJob $job, PDOStatement $statement)
    {
        $payload = '{"test":"data"}';
        $job->serialise()->shouldBeCalled()->willReturn($payload);

        $this->pdo->prepare(Argument::containingString(
            "INSERT INTO phq_jobs (`class`, `payload`) VALUES (?, ?)")
        )->shouldBeCalled()->willReturn($statement);

        $statement->execute(Argument::size(2))->shouldBeCalled()->willReturn(true);

        $this->enqueue($job);
    }

    function it_should_be_able_to_return_the_next_available_job(PDOStatement $statement)
    {
        $this->pdo->prepare(Argument::containingString(
            "SELECT `id`,`class`,`payload`,`status`, `retries`\n            FROM phq_jobs\n            WHERE status = ?"
        ))->shouldBeCalled()->willReturn($statement);

        $statement->execute(Argument::containing(Job::STATUS_IDLE))->shouldBeCalled()->willReturn(true);

        $statement->fetch(\PDO::FETCH_ASSOC)->shouldBeCalled()->willReturn([
            "id" => 4,
            "class" => "TestClass",
            "payload" => "abc",
            "status" => 0,
            "retries" => 0
        ]);

        $this->getNext()->shouldBeAnInstanceOf(JobDataset::class);
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
}
