<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 16:39
 */

namespace PHQ\Storage;


/**
 * Indicates that a storage handler requires a pre-setup phase before the application can be used
 * Interface IQueueStorageNeedsSetup
 * @package PHQ\Storage
 */
interface IQueueStorageNeedsSetup
{
    /**
     * This method will be called for the initial application environment setup required to make this storage
     * adapter function.
     */
    public function setup() : void;
}