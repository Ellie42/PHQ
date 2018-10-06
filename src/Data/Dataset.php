<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 08:53
 */

namespace PHQ\Data;


use PHQ\Traits\ArrayFilters;

class Dataset
{
    use ArrayFilters;

    public function __construct(array $props = [])
    {
        $this->hydrate($props);
    }

    /**
     * Set all class properties from an array with whitelisted keys
     * @param array $props
     * @param array $whitelistKeys
     */
    public function hydrate(array $props, array $whitelistKeys = [])
    {
        //Whitelist is optional as if a property does not exist on the class it will throw an error
        if(count($whitelistKeys) === 0){
            $whitelisted = $props;
        }else{
            $whitelisted = $this->getWhitelistedValues($props, $whitelistKeys);
        }

        foreach ($whitelisted as $key => $val) {
            $setterName = "set" . ucfirst($key);

            //Here we can use the __call implementation which fakes setters to set our value
            $this->$setterName($val);
        }
    }

    /**
     * Handle all (get|set){PROPERTY_NAME}() method calls, allows for uniform access from outside the class without
     * worrying about how a particular property is implemented.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $propName = lcfirst(substr($name, 3));
        $className = static::class;

        if (!property_exists($this, $propName)) {
            throw new \BadMethodCallException("Property $propName does not exist on class $className");
        }

        $methodPrefix = substr($name, 0, 3);

        if ($methodPrefix === "get") {
            return $this->$propName;
        } else if ($methodPrefix === "set") {
            if (count($arguments) < 1) {
                throw new \BadMethodCallException("Setter ${name} cannot be called without a value argument!");
            }

            return $this->$propName = $arguments[0];
        }

        throw new \BadMethodCallException("Method $name does not exist on class $className");
    }

    /**
     * Return an array of all data in the dataset
     * @return array
     */
    public function toArray()
    {
        return array_filter(get_object_vars($this),function($val){
            return $val !== null;
        });
    }
}