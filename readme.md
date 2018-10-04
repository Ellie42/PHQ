# PHQ

Job queue system written in PHP

## Setup

Firstly choose a storage medium for the queue data, and implement or use an existing class that implements `\PHQ\Storage\IQueueStorageHandler`.

```php
    $pdo = new PDO(...);
    $storage = new MySQLQueueStorage($pdo);
    
    /**
    * $storage can be any class that implements \PHQ\Storage\IQueueStorageHandler
    */
    $phq = new \PHQ\PHQ($storage);
    
```

### Creating Jobs

Create a new job type that extends `\PHQ\Jobs\Job` and implement at least the `run` method.
`run` must return an integer that relates to the statuses defined as `\PHQ\Jobs\Job::STATUS_*`;

```php
class MyJob extends \PHQ\Jobs\Job{
    run(): int{
        return \PHQ\Jobs\Job::STATUS_SUCCESS;
    }
}
```

### Adding jobs to queue

Add jobs to the queue(managed by the chosen storage handler) by calling `enqueue(IJob)` on `PHQ\PHQ`.

```php
    $phq->enqueue(new MyJob());
``` 

### Getting jobs from the queue

Retrieve the next job by calling `getNext()` on `PHQ\PHQ`

```php
    /**
    * $job is an instance of \PHQ\Jobs\IJob
    */
    $job = $phq->getNext();
```