<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 07/10/2018
 * Time: 10:23
 */

namespace PHQ\Config;


use PHQ\Data\Dataset;
use PHQ\Exceptions\ConfigurationException;

class WorkerConfig extends Dataset
{
    public $count = 1;

    public $script = __DIR__ . "/../Workers/scripts/workerScript.php";

    public $command = null;

    public $language = null;

    protected $interpreters = [
        "php" => "php",
        "js|javascript" => "node",
        "ts|typescript" => "ts-node"
    ];

    public function getScriptCommand(): string
    {
        if ($this->command !== null) {
            return $this->command;
        }

        $interpreter = null;

        if ($this->language !== null) {
            $interpreter = $this->getInterpreterByLanguage($this->language);
        } else {
            $foundFileExtension = preg_match("/\.([^\/]*)$/", $this->script, $matches);

            if ($foundFileExtension) {
                $interpreter = $this->getInterpreterByLanguage($matches[1]);
            }
        }

        if($interpreter === null){
            throw new ConfigurationException(
                "Worker script interpreter could not be determined, specify 'language','command', or add file extension");
        }

        return "{$interpreter} {$this->script}";
    }

    /**
     * Return the interpreter command from a language name or shorthand
     * @param $language
     * @return mixed|null
     */
    private function getInterpreterByLanguage($language)
    {
        foreach ($this->interpreters as $test => $command) {
            if (preg_match("/$test/", $language)) {
                return $command;
            }
        }

        return null;
    }
}