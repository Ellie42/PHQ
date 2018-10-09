<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 09/10/2018
 * Time: 11:04
 */

namespace PHQ\Config;


use PHQ\Data\Dataset;
use PHQ\EventBus\PeriodicEventBus;

class EventBusConfig extends Dataset
{
    /**
     * Class name of the event bus to use
     * @var string
     */
    public $class = PeriodicEventBus::class;

    /**
     * All implementation specific options
     * @var array
     */
    public $options = [];
}