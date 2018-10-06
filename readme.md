# PHQ [![Build Status](https://travis-ci.com/Ellie42/PHQ.svg?branch=master)](https://travis-ci.com/Ellie42/PHQ) [![codecov](https://codecov.io/gh/Ellie42/PHQ/branch/master/graph/badge.svg)](https://codecov.io/gh/Ellie42/PHQ)


Job queue system written in PHP

## Getting Started

Firstly choose a storage medium for the queue data, and implement or use an existing class that implements `\PHQ\Storage\IQueueStorageHandler`.

```php
    $pdo = new PDO(...);
    $storage = new MySQLQueueStorage($pdo);
    
    /**
    * $storage can be any class that implements \PHQ\Storage\IQueueStorageHandler
    * Only required if not specified in the configuration file, otherwise, this instance will override
    * the configured storage
    */
    $phq = new \PHQ\PHQ($storage);
    
```

Before PHQ can be used some storage adapters may require initial setup.
Call `setup()` on `PHQ\PHQ` after adding the storage adapter configuration to run
the initial setup.

This will be added as a setup script in composer in the future.

```php
    $phq->setup();
```


### Creating Jobs

Create a new job type that extends `\PHQ\Jobs\Job` and implement at least the `run` method.
`run` must return an integer that relates to the statuses defined as `\PHQ\Jobs\Job::STATUS_*`;

```php
class MyJob extends \PHQ\Jobs\Job{
    public function run(): int{
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

## Configuration

PHQ will automatically load configuration data stored in `${cwd}/phqconf.php`.

Example:
```php
    <?php
    
    return [
        
        //This will override the system ENVIRONMENT variable if set 
        "environment" => "test",
        
        //Storage handler configuration, if this is specified then creating a new instance of PHQ
        //will no longer require the IQueueStorageHandler parameter  
        "storage" => [
            
            //This should be a class name of a subclass of IQueueStorageHandler
            "handler" => \PHQ\Storage\MySQLQueueStorage::class,
            
            //These are options specific to the storage handler itself and will be passed directly 
            //to it as an array, so long as it implements the IQueueStorageConfigurable interface
            "options" => [
                 
                //The key here is always the environment that the options should be valid for
                "test" => [
                    
                    //All data within this array will be passed to the IQueueStorageConfigurable 
                    //init() method
                    "host" => "localhost",
                    "port" => 14783,
                    "user" => "root",
                    "pass" => "root",
                    "database" => "phq"
                    
                ] 
            ]
        ]
    ];
```
