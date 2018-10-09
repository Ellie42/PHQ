<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 09/10/2018
 * Time: 11:55
 */

namespace PHQ\Config\Options;


use PHQ\Data\Dataset;

class PeriodicEventBusOptions extends Dataset
{
    /**
     * Time between updates in seconds
     * @var int
     */
    public $interval = 5;
}