# PHQ [![Build Status](https://travis-ci.com/Ellie42/PHQ.svg?branch=master)](https://travis-ci.com/Ellie42/PHQ) [![codecov](https://codecov.io/gh/Ellie42/PHQ/branch/master/graph/badge.svg)](https://codecov.io/gh/Ellie42/PHQ)


Job queue system written in PHP

## Getting Started
See: [Basic Application Example](examples/basic/application.php)

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

### Payloads

Jobs can use a custom payload class which will hold all the properties that the job needs.
This allows strict contracts of which data can/will be provided to the job.

#### Creating a payload

A payload class must extend `\PHQ\Data\Payload` and should define it's allowed values
as public properties.

A payload is an extension of `\PHQ\Data\Dataset` and so can define setters and getters (see: [Datasets](#datasets))

```php
class MyJobPayload extends \PHQ\Data\Payload{
    public $propA;
}
```

Jobs have access to the data that they were created with as a property on `PHQ\Jobs\Job`

```php
class MyJob extends \PHQ\Jobs\Job{
    public function run(): int{
        $jobPayload = $this->getPayload(MyJobPayload::class);
        
        return \PHQ\Jobs\Job::STATUS_SUCCESS;
    }
}

```

### Configuring Jobs

You can create and enqueue a job with no properties however if you need some more specific data for the job to run you can 
pass the payload as the first parameter when instantiating a job.

```php
    $job = new MyJob(new MyJobPayload(["propA" => "propB"]));
```

If for some reason, such as when creating a new storage handler, you need to update the base Job properties, you can 
instead pass an instance of `\PHQ\Data\JobDataset` as the first parameter which will allow you to update the `class`, `status`,
`retries` and `payload` properties directly.

```php
    $job = new MyJob(new JobDataset(["status" => \PHQ\Jobs\Job::STATUS_IDLE,"payload" => ["a" => "b"]]]));
```

### Adding jobs to queue

Add jobs to the queue(managed by the chosen storage handler) by calling `enqueue(IJob)` on `PHQ\PHQ`.

```php
    $phq->enqueue(new MyJob());
``` 

## Running the workers
See: [Worker Runner Example](examples/basic/workers.php)

The actual worker process should be separate from the application configuring/adding the jobs,
you can run the `$phq->start()` method to start processing jobs.

```php
$phq->start()

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
        ],
        
        //Workers will run the jobs in their own processes
        "workers" => [
            
            //Number of worker processes to spawn
            "count"  => 2,
            
            //Optional - Specify a worker script to act as the worker process
            "script" => "myWorker.php",
            
            //Optional - Language of worker script(if script does not have a file extension)
            // or if overriding the file extension
            "language" => "php",
            
            //Optional - Command to run for each worker, this is an explicit command
            // which overrides script and language options
            "command" => "php myWorker.php --work-hard-please"     
        ],
        
        //EventBus is the class that is responsible for receiving update events from the application
        //and forwarding them to the workermanager.
        //By default the EventBus is a PeriodicJobEventBus which will attempt to update the jobs every n seconds
        //By specifying another EventBus you can send status updates using many different methods.
        //e.g. HTTPJobEventBus - Start a HTTP server and connect to it from the application to send updates
        //Optional
        "eventbus" => [
            //Optional - Classname of the required EventBus
            "class" => \PHQ\EventBus\PeriodicEventBus::class,
            "options" => [
                //Implementation specific EventBus options go here
                //e.g. "interval" => 10 //for periodic event bus update interval
            ]   
        ]
    ];
```

## EventBus List
### PeriodicEventBus
Requests a job list update periodically.

Available options:

```php
[
    //Update time in seconds
    "interval" => 5
]
```

### Datasets

Datasets are any class that extends `PHQ\Data\Dataset` and are used purely as data containers. 
The allowed keys are defined by adding public properties to your class and should be accessed using setters and getters.

Setters and getters are automatically handled by the dataset, just call `(set|get)ucfirst($propertyName)` eg. `setName($val)`, `getName()`.

You can override the setters and getters but declaring the methods in the child class.

```php
class CustomDataset extends \PHQ\Data\Dataset{
    public $propertyA;
    public $propertyB;
}

```
You can pass the data to fill the dataset with as the constructor parameter as an array of `$property => $value`.
Attempting to pass data this with property names that do not exist on the dataset will throw an exception.

```php
$data = new CustomDataset([
    "propertyA" => "AAA"
]);

$data->setPropertyB("BBB");

// "AAA"
echo $data->getPropertyA();

/**
*   You can also call toArray() on a dataset to return all properties as a pure array.
*/
var_dump($data->toArray());
```

## TODO

Refactor \PHQ. It has far too much responsibility and should instead push everything queue related onto delegates.

Modify WorkerContainer to use a generic ProcessAdapter rather than the Process class directly. This is to allow other methods for spawning worker processes, such as over the network.

Send job ID across all worker messages 