<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:40
 */

namespace PHQ\Data;


use ArrayIterator;
use PHQ\Exceptions\TypeError;

/**
 * Used for implementing arrays with strict type checking against class names
 * Class ObjectArray
 * @package PHQ\Data
 */
class ObjectArray extends ArrayIterator
{
    /**
     * The type(classname) to check against
     * @var null | string
     */
    protected $type = null;

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws TypeError
     */
    public function offsetSet($offset, $value)
    {
        //Only accept object types
        if (!is_object($value)) {
            throw new TypeError("Value must be an object!");
            //If a class type is specified then apply strict checking
        } else if ($this->type !== null && !($value instanceof $this->type)) {
            $typeGiven = get_class($value);
            throw new TypeError("Object must be of type {$this->type}, type {$typeGiven} given");
        }

        parent::offsetSet($offset, $value);
    }
}