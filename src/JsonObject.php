<?php namespace DCarbone;

/*
    OO Json object building for PHP
    Copyright (C) 2012-2018  Daniel Paul Carbone

    This Source Code Form is subject to the terms of the Mozilla Public
    License, v. 2.0. If a copy of the MPL was not distributed with this
    file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
 * Class JsonObject
 * @package DCarbone
 */
class JsonObject implements \Serializable, \JsonSerializable
{
    /**
     * The data housed in this object
     * @var array|\stdClass
     */
    protected $data = null;

    /**
     * Currently Accessed Element
     * @var array|\stdClass
     */
    protected $current = null;

    /**
     * Most recent key set in an object
     * @var string
     */
    protected $currentObjKey = null;

    /**
     * Keys to get to current element
     * @var array
     */
    protected $pathKeys = array();

    /**
     * Initialize a new object
     *
     * @throws \RuntimeException
     * @return bool
     * @throws \Exception
     */
    public function startObject()
    {
        $obj = new \stdClass;

        if ($this->current === null && $this->data === null)
        {
            $this->data = $obj;
            $this->current = &$this->data;
            return true;
        }

        if (is_array($this->current))
            return $this->appendToArray($obj);

        if (is_object($this->current))
            return $this->appendToObject($obj);

        // If we reach this point, something weird has happened
        throw new \RuntimeException('In-memory JSON representation is unstable');
    }

    /**
     * 'End' object
     *
     * @throws \Exception
     * @return bool
     */
    public function endObject()
    {
        if (!is_object($this->current))
            throw new \Exception('Cannot end non-object with endObject');

        array_pop($this->pathKeys);
        $this->identifyCurrent();
        return true;
    }

    /**
     * Initialize Array
     *
     * @throws \Exception
     * @return bool
     */
    public function startArray()
    {
        if ($this->current === null && $this->data === null)
        {
            $this->data = array();
            $this->current = &$this->data;
            return true;
        }

        if (is_array($this->current))
            return $this->appendToArray(array());

        if (is_object($this->current))
            return $this->appendToObject(array());

        // If we reach this point, something weird has happened
        throw new \Exception('In-memory JSON representation is unstable');
    }

    /**
     * 'End' Array
     *
     * @throws \Exception
     * @return bool
     */
    public function endArray()
    {
        if (!is_array($this->current))
            throw new \Exception('Cannot end non-array with endArray');

        array_pop($this->pathKeys);
        $this->identifyCurrent();
        return true;
    }

    /**
     * Write new property for Object
     *
     * @param  string $propertyName  Name of property
     * @throws \Exception
     * @return bool
     */
    public function writeObjectPropertyName($propertyName)
    {
        if (!is_string($propertyName) && !is_int($propertyName))
            throw new \Exception('Can only assign string or integer values to object property name!');

        if (!is_object($this->current))
            throw new \Exception('Tried to write property value to non-object');

        if ($this->currentObjKey !== null)
            throw new \Exception('Cannot define new property without populating previous one');

        $this->current->$propertyName = null;
        $this->currentObjKey = $propertyName;
        return true;
    }

    /**
     * Write value to Array or Object
     *
     * @param  string $value  Value to write
     * @throws \Exception
     * @return bool
     */
    public function writeValue($value)
    {
        if (!is_scalar($value))
            throw new \Exception('Tried to write invalid value');

        if (is_array($this->current))
            return $this->writeValueToArray($value);

        if (is_object($this->current))
            return $this->writeValueToObject($value);

        throw new \Exception('In-memory JSON representation is unstable');
    }

    /**
     * Returns the data
     *
     * @return \stdClass|array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array(
            $this->data,
            $this->currentObjKey,
            $this->pathKeys
        ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized The string representation of the object.
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->data = $unserialized[0];
        $this->currentObjKey = $unserialized[1];
        $this->pathKeys = $unserialized[2];
        $this->identifyCurrent();
    }

    /**
     * @return array|\stdClass
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    // --------------------------

    /**
     * Append new Array or Object to current Object
     *
     * @param  mixed $data  Array or Object being appended
     * @throws \Exception
     * @return bool
     */
    protected function appendToObject($data)
    {
        if ($this->currentObjKey === null)
            throw new \Exception('Cannot assign object value without first assigning key to object');

        $key = $this->currentObjKey;
        $this->current->$key = $data;
        $this->currentObjKey = null;
        $this->pathKeys[] = $key;
        $this->identifyCurrent();
        return true;
    }

    /**
     * Append new Array or Object to current Array
     *
     * @param  mixed  $data  Array or Object being appended
     * @return bool
     */
    protected function appendToArray($data)
    {
        $count = array_push($this->current, $data);
        $this->pathKeys[] = ($count - 1);
        $this->identifyCurrent();
        return true;
    }

    /**
     * Appends string value to array
     *
     * @param  string  $value  string to write
     * @return bool
     */
    protected function writeValueToArray($value)
    {
        $this->current[] = $value;
        return true;
    }

    /**
     * Appends string value to object
     *
     * @param  string $value  Value to write
     * @throws \Exception
     * @return bool
     */
    protected function writeValueToObject($value)
    {
        if ($this->currentObjKey === null)
            throw new \Exception('Tried to define value without first defining key');

        $key = $this->currentObjKey;
        $this->current->$key = $value;
        $this->currentObjKey = null;
        return true;
    }

    /**
     * Find the parent of the current element
     *
     * @return void
     */
    protected function identifyCurrent()
    {
        unset($this->current);

        $current = &$this->data;

        foreach($this->pathKeys as $pathKey)
        {
            if (is_object($current))
                $current = &$current->$pathKey;
            else if (is_array($current))
                $current = &$current[$pathKey];
        }
        $this->current = &$current;
    }
}