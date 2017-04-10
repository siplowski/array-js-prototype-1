<?php

namespace Horat1us\Arrays;

use Horat1us\Arrays\Traits\CopyWithin;
use Horat1us\Arrays\Traits\Fill;
use Horat1us\Arrays\Traits\Pop;
use Horat1us\Arrays\Traits\Push;
use Horat1us\Arrays\Traits\Reverse;
use Horat1us\Arrays\Traits\Shift;
use Horat1us\Arrays\Traits\Sort;
use Horat1us\Arrays\Traits\Splice;
use Horat1us\Arrays\Traits\Unshift;
use Traversable;

/**
 * Class Collection
 * @package Horat1us\Arrays
 */
class Collection implements \ArrayAccess, \Serializable, \IteratorAggregate, \Countable
{
    use CopyWithin, Fill, Pop, Push, Reverse, Shift, Sort, Splice, Unshift;

    /**
     * @var array
     */
    protected $container;

    public $length;

    public function __construct(...$args)
    {
        $this->init(...$args);
    }

    /**
     * @param array $args
     * @return void
     */
    protected function init(...$args)
    {
        if (count($args) === 1 && is_int($args[0])) {
            $this->container = array_fill(0, $args[0], null);
            return;
        } elseif (count($args) === 1 && is_array($args[0])) {
            $this->container = $args[0];
            return;
        } else {
            $this->container = $args;
        }
        $this->initLength();
    }

    /**
     * Copying JS-like .length behavior
     */
    protected function initLength()
    {
        if (!$this->container) {
            $this->length = 0;
            return;
        }

        $count = count($this->container);
        $keys = array_keys($this->container);
        $this->length = (array_keys($this->container) === range(0, $count - 1))
            ? $count
            : max($keys);
    }

    // region Statics

    /**
     * @param array ...$args
     * @return static
     */
    final public static function create(...$args)
    {
        return new static(...$args);
    }

    /**
     * @param array $arrayLike
     * @param \Closure|null $mapFn
     * @param object|null $thisArg
     * @return Collection
     */
    final public static function from(array $arrayLike, \Closure $mapFn = null, object $thisArg = null): Collection
    {
        if ($mapFn) {
            if ($thisArg) {
                $mapFn->bindTo($thisArg);
            }
            $arrayLike = array_map($mapFn, $arrayLike);
        }
        return new static($arrayLike);
    }

    /**
     * @param mixed $obj
     * @return bool
     */
    final public static function isArray($obj): bool
    {
        return is_array($obj) || $obj instanceof \ArrayAccess;
    }

    /**
     * @param array ...$elements
     * @return static
     */
    final public static function of(...$elements)
    {
        $elements[] = null;
        $instance = new static(...$elements);
        unset($instance[count($instance) - 1]);
        return $instance;
    }
    // endregion
    // region Interfaces
    // region ArrayAccess Interface

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
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
        return array_key_exists($offset, $this->container);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
            if (is_numeric($offset)) {
                $this->length = (int)$offset;
            }
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    // endregion

    // region Serializable
    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return json_encode($this->container);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->init(json_decode($serialized, true));
    }
    // endregion

    // region IteratorAggregate
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->container);
    }
    // endregion
    // region Countable
    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->container);
    }
    // endregion
    // endregion
}