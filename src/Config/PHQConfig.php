<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 10:25
 */

namespace PHQ\Config;


class PHQConfig
{
    /**
     * Contains the raw config array from phqconf.php
     * @var array
     */
    protected $rawConfig = [];

    /**
     * Contains the current storage handler configuration
     * @var StorageHandlerConfig
     */
    protected $storageHandlerConfig;

    /**
     * Environment
     * @var string
     */
    protected $env;

    /**
     * Root to search for the config file from
     * @var string
     */
    private $rootPath;

    public function __construct(
        string $rootPath,
        StorageHandlerConfig $storageHandlerConfig = null
    )
    {
        $this->storageHandlerConfig = $storageHandlerConfig;
        $this->rootPath = $rootPath;
    }

    /**
     * Load the config file if it exists at {cwd() . '/phqconf.php'}
     */
    public function load()
    {
        if (!file_exists($this->rootPath . "/phqconf.php")) {
            return;
        }

        $this->rawConfig = require $this->rootPath . "/phqconf.php";

        if (isset($this->rawConfig['environment'])) {
            $this->env = $this->rawConfig['environment'];
        } else {
            $this->env = getenv('ENVIRONMENT');
        }
    }

    /**
     * @return array
     */
    public function getRawConfig(): array
    {
        return $this->rawConfig;
    }

    /**
     * Return the storage handler configuration or create a new one based on the configuration file
     * @return StorageHandlerConfig | null
     */
    public function getStorageConfig(): ?StorageHandlerConfig
    {
        //Config has already been created
        if ($this->storageHandlerConfig !== null) {
            return $this->storageHandlerConfig;
        }

        //Raw config does not contain any storage configuration
        if (!isset($this->rawConfig['storage'])) {
            return null;
        }

        $config = $this->rawConfig['storage'];
        $options = [];

        //Environment specific options for the storage handler
        if (isset($config['options'][$this->env])) {
            $options = $config['options'][$this->env];
        }

        return $this->storageHandlerConfig = new StorageHandlerConfig(
            $config['handler'],
            $options
        );
    }
}