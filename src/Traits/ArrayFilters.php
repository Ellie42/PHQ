<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 05/10/2018
 * Time: 10:10
 */

namespace PHQ\Traits;


/**
 * Trait which will contain various commonly used array filtering techniques
 * Trait ArrayFilters
 * @package PHQ\Traits
 */
trait ArrayFilters
{
    /**
     * Return the whitelisted entries of an array
     * @param array $props
     * @param array $whitelistKeys
     * @return array
     */
    protected function getWhitelistedValues(array $props, array $whitelistKeys): array
    {
        $whitelisted = array_intersect_key($props, array_flip($whitelistKeys));

        return $whitelisted;
    }
}