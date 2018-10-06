<?php

namespace PHQ\Traits;

use PHQ\Exceptions\AssertionException;

/**
 * General assertion methods which throw exceptions on failure
 * Trait Assertions
 * @package PHQ\Traits
 */
trait Assertions
{
    protected function assertKeysInArray(array $array, array $keys){
        if(count(array_intersect_key($array, array_flip($keys))) < count($keys)){
            throw new AssertionException("Array does not contain all required keys≠");
        }
    }
}