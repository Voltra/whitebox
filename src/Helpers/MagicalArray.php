<?php

namespace WhiteBox\Helpers;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;
use Traversable;

class MagicalArray implements IteratorAggregate, ArrayAccess, Countable, Serializable{

    protected $array;
    protected $default;

    public function __construct(array $arr=[], $default=""){
        $this->array = $arr;
        $this->default = $default;
    }

    public function __invoke($key){
        if(isset($this->array[$key]))
            return $this->array[$key];
        else
            return $this->default;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(){
        return new ArrayIterator($this->array);
    }

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
    public function offsetExists($offset){
        return isset($this->array[$offset]);
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
    public function offsetGet($offset){
        return $this->__invoke($offset);
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
    public function offsetSet($offset, $value){
        if(is_null($offset))
            $this->array[] = $value;
        else
            $this->array[$offset] = $value;
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
    public function offsetUnset($offset){
        unset($this->array[$offset]);
    }


    public function reduce(callable $reducer, $acc){
        //return new MagicalArray(array_reduce($this->array, $reducer, $acc));

        foreach ($this as $elem)
            $acc = $reducer($acc, $elem);

        return $acc;
    }

    public function map(callable $mapper){
        //return new MagicalArray( array_map($mapper, $this->array) );
        $arr = [];
        foreach($this as $elem)
            $arr[] = $mapper($elem);

        return new MagicalArray($arr, $this->default);
    }

    public function filter(callable $predicate){
        //return new MagicalArray( array_filter($this->array, $predicate) );
        $arr = [];
        foreach ($this as $elem) {
            if ($predicate($elem))
                $arr[] = $elem;
        }

        return new MagicalArray($arr, $this->default);
    }

    public function forEach(callable $procedure){
        foreach($this->array as $elem)
            $procedure($elem);

        return $this;
    }


    public function size(){
        return $this->count();
    }

    public function count(){
        return count($this->array);
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->array);
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
    public function unserialize($serialized){
        self::__construct(unserialize($serialized));
    }

    public static function fromJson(string $json){
        return new MagicalArray(json_decode($json, true));
    }

    public function toJson(){
        return json_encode($this->array);
    }
}