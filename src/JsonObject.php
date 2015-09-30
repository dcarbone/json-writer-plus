<?php namespace DCarbone;

    /*

    OO Json object building for PHP
    Copyright (C) 2012-2015  Daniel Paul Carbone

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    */

/**
 * Class JsonObject
 * @package DCarbone
 */
class JsonObject
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
     * Append new Array or Object to current Object
     *
     * @param  mixed $data  Array or Object being appended
     * @throws \Exception
     * @return bool
     */
    protected function appendToObject($data)
    {
        if ($this->currentObjKey === null)
            throw new \Exception('Cannot assign value without first assigning key to object');

        $key = $this->currentObjKey;
        $this->current->$key = $data;
        $this->current = &$this->current->$key;
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
        $this->current = &$this->current[($count-1)];
        $this->pathKeys[] = ($count - 1);
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
            throw new \Exception('Can only assign string or Integer values to object property name!');

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

        foreach($this->pathKeys as $I=>$pathKey)
        {


            if (is_object($current))
                $current = &$current->$pathKey;
            else if (is_array($current))
                $current = &$current[$pathKey];
        }
        $this->current = &$current;
    }

    /**
     * Returns the data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}