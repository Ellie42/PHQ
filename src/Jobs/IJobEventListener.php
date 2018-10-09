<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 09/10/2018
 * Time: 10:38
 */

namespace PHQ\Jobs;


interface IJobEventListener
{
    /**
     * Handle the event that a job is added, it can be provided with the specific id or it might be
     * just a general update
     * @param int|null $id
     * @return mixed
     */
    function onJobAdded(?int $id = null);

    /**
     * Force update of jobs
     * @return mixed
     */
    public function updateJobs();
}