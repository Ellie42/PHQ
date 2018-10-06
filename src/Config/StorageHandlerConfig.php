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

    /**
     * @var IQueueStorageHandler
     */
    private $instance = null;

    public function __construct(string $class, array $options)
    {
        $this->class = $class;
        $this->options = $options;
    }

    public function setHandlerInstance(IQueueStorageHandler $instance){
        $this->instance = $instance;
    }

    /**
     * Instantiate and return the storage handler from the configuration
     * @throws ConfigurationException
     */
    public function getStorage() : IQueueStorageHandler
    {
        if($this->instance === null){
            if (!is_subclass_of($this->class, IQueueStorageHandler::class)) {
                throw new ConfigurationException("{$this->class} is not a valid storage handler, must implement " . IQueueStorageHandler::class);
            }

            $this->instance = new $this->class();
        }

        if($this->instance instanceof IQueueStorageConfigurable){
            $this->instance->init($this->options);
        }

        return $this->instance;
    }
}