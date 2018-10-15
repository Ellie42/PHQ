<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 15/10/2018
 * Time: 10:06
 */

namespace PHQ\Workers;


use PHQ\Messages\Worker\JobFinishedMessage;

/**
 * Handler for all job events triggered by workers
 * Interface IWorkerEventHandler
 * @package PHQ\Workers
 */
interface IWorkerEventHandler
{
    function onJobCompleted(JobFinishedMessage $jobFinishedMessage): void;
}