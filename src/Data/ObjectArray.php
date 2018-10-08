<?php
/**
 * Created by PhpStorm.
 * User: sophiegauthier
 * Date: 08/10/2018
 * Time: 08:40
 */

namespace PHQ\Data;


use PHQ\Exceptions\TypeError;

/**
 * Used for implementing arrays with strict type checking against class names
 * Class ObjectArray
 * @package PHQ\Data
 */
class ObjectArray implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $objects = [];

    /**
     * The type(classname) to check against
     * @var null | string
     */
    protected $type = null;

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->objects[$offset];
    }

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
        }else if ($this->type !== null && !($value instanceof $this->type)) {
            $typeGiven = get_class($value);
            throw new TypeError("Object must be of type {$this->type}, type {$typeGiven} given");
        }

        $this->objects[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }
}