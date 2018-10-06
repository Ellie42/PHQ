<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 16:39
 */

namespace PHQ\Storage;


interface IQueueStorageNeedsSetup
{
    /**
     * This method will be called for the initial application environment setup required to make this storage
     * adapter function.
     */
    public function setup() : void;
}