<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:40
 */

namespace PHQ\Workers;


use PHQ\Data\ObjectArray;

class WorkerContainerArray extends ObjectArray
{
    protected $type = WorkerContainer::class;
}