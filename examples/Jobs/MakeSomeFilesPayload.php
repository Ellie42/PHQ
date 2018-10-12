<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 06/10/2018
 * Time: 14:55
 */

namespace PHQExamples\Jobs;

class MakeSomeFilesPayload extends \PHQ\Data\Payload
{
    public $dir;
    public $fileCount;
}