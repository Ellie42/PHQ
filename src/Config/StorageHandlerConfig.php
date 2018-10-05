<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 11:26
 */

namespace PHQ\Config;


use PHQ\Exceptions\ConfigurationException;
use PHQ\Storage\IQueueStorageConfigurable;
use PHQ\Storage\IQueueStorageHandler;

class StorageHandlerConfig
{
    /**
     * Storage handler class name
     * @var string
     */
    public $class;

    /**
     * Array of options
     * @var array
     */
    public $options;

    public function __construct(string $class, array $options)
    {
        $this->class = $class;
        $this->options = $options;
    }

    /**
     * Instantiate and return the storage handler from the configuration
     * @throws ConfigurationException
     */
    public function getStorage() : IQueueStorageHandler
    {
        $className = $this->class;

        if (!is_subclass_of($className, IQueueStorageHandler::class)) {
            throw new ConfigurationException("$className is not a valid storage handler, must implement " . IQueueStorageHandler::class);
        }

        $obj = new $className();

        if($obj instanceof IQueueStorageConfigurable){
            $obj->init($this->options);
        }

        return $obj;
    }
}