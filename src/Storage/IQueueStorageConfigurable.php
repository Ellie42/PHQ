<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 11:24
 */

namespace PHQ\Storage;


/**
 * Indicates that a storage handler can be configured from the phqconfig file
 * Interface IQueueStorageConfigurable
 * @package PHQ\Storage
 */
interface IQueueStorageConfigurable
{
    /**
     * Initialise the storage handler with the config
     * @param array $options
     */
    public function init(array $options): void;
}