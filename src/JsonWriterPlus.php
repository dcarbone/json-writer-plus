<?php namespace DCarbone;

/*
    OO Json object building for PHP
    Copyright (C) 2012-2018  Daniel Paul Carbone

    This Source Code Form is subject to the terms of the Mozilla Public
    License, v. 2.0. If a copy of the MPL was not distributed with this
    file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
 * Class JsonWriterPlus
 * @package DCarbone
 */
class JsonWriterPlus
{
    /**
     * JsonObject Instance
     * @var JsonObject
     */
    protected $json = null;

    /**
     * Has the output been initialized?
     * @var boolean
     */
    protected $started = false;

    /**
     * Has the output been finalized?
     * @var boolean
     */
    protected $ended = false;

    /**
     * Iterates Comments
     * @var integer
     */
    protected $commentCount = 0;

    /**
     * Append a new object to the JSON output
     *
     * @return  bool
     * @throws \Exception
     */
    public function writeStartObject()
    {
        if ($this->canEdit())
            return $this->json->startObject();

        return false;
    }

    /**
     * End current object
     *
     * @return  bool
     * @throws \Exception
     */
    public function writeEndObject()
    {
        if ($this->canEdit())
            return $this->json->endObject();

        return false;
    }

    /**
     * Append new Array
     *
     * @return  bool
     * @throws \Exception
     */
    public function writeStartArray()
    {
        if ($this->canEdit())
            return $this->json->startArray();

        return false;
    }

    /**
     * End current Array
     *
     * @return  bool
     * @throws \Exception
     */
    public function writeEndArray()
    {
        if ($this->canEdit())
            return $this->json->endArray();

        return false;
    }

    /**
     * Write object property
     *
     * @param   string $property Property Name
     * @return  bool
     * @throws \Exception
     */
    public function writeObjectPropertyName($property)
    {
        if ($this->canEdit() && (is_string($property) || is_int($property)))
            return $this->json->writeObjectPropertyName($property);

        return false;
    }

    /**
     * Defines a property with value of string
     *
     * @param   string $property Name of Property
     * @param   string $value Value of Property
     * @return  Bool
     * @throws \Exception
     */
    public function writeObjectProperty($property, $value)
    {
        if ($this->canEdit())
        {
            return $this->writeObjectPropertyName($property) &&
            $this->writeValue($value);
        }

        return false;
    }

    /**
     * Write string Value to Array or Object
     *
     * @param   string $value Value
     * @throws \InvalidArgumentException
     * @return  bool
     * @throws \Exception
     */
    public function writeValue($value)
    {
        if ($this->canEdit())
        {
            // If a non-scalar value is passed in (such as a class object)
            // try to convert it to string.  At this point, writeValue MUST be scalar!
            if (!is_scalar($value))
            {
                try {
                    $typecast = settype($value, 'string');
                }
                catch (\Exception $e) {
                    throw new \InvalidArgumentException('Cannot cast non-scalar value to string (did you forget to define a __toString on your object?)', null, $e);
                }
                
                if ($typecast === false)
                    throw new \InvalidArgumentException('Cannot cast non-scalar value to string (did you forget to define a __toString on your object?)');
            }

            if (is_string($value))
                $value = $this->encodeString($value);

            return $this->json->writeValue($value);
        }

        return false;
    }

    /**
     * @param mixed $object
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function appendObject($object)
    {
        if ($this->canEdit())
        {
            if (!is_object($object))
                throw new \InvalidArgumentException('Passed non-object to appendObject');

            $this->writeStartObject();
            foreach($object as $key=>$value)
            {
                if (is_scalar($value))
                {
                    $this->writeObjectProperty($key, $value);
                }
                else if (is_array($value))
                {
                    $this->writeObjectPropertyName($key);
                    $this->appendArray($value);
                }
                else if (is_object($value))
                {
                    $this->writeObjectPropertyName($key);
                    $this->appendObject($value);
                }
                else
                {
                    throw new \InvalidArgumentException('Value of type '.gettype($value).' seen during appendObject call');
                }
            }
            $this->writeEndObject();

            return true;
        }

        return false;
    }

    /**
     * @param array $array
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function appendArray(array $array)
    {
        if ($this->canEdit())
        {
            $objectOpened = false;
            $this->writeStartArray();
            foreach($array as $k=>$v)
            {
                if (is_string($k) && false === ctype_digit($k))
                {
                    $this->writeStartObject();
                    $objectOpened = true;
                    $this->writeObjectPropertyName($k);
                }

                if (is_scalar($v))
                    $this->writeValue($v);
                else if (is_array($v))
                    $this->appendArray($v);
                else if (is_object($v))
                    $this->appendObject($v);
                else
                    throw new \InvalidArgumentException('Value of type '.gettype($v).' seen during appendArray call');

                if ($objectOpened)
                {
                    $this->writeEndObject();
                    $objectOpened = false;
                }
            }

            $this->writeEndArray();

            return true;
        }
        return false;
    }

    /**
     * Initialize Data
     *
     * @return  bool
     */
    public function startJson()
    {
        if (!$this->started)
        {
            $this->json = new JsonObject;
            return $this->started = true;
        }
        return false;
    }

    /**
     * End current JsonObject editing
     *
     * @return  bool
     */
    public function endJson()
    {
        if (!$this->ended)
            return $this->ended = true;

        return false;
    }

    /**
     * @deprecated Use getEncoded instead
     * @param int $options
     * @return string
     * @throws \Exception
     */
    public function getJsonString($options = 0)
    {
       return $this->getEncoded($options);
    }

    /**
     * Get encoded json string of current JsonObject state
     *
     * @link  http://php.net/manual/en/function.json-encode.php
     * @link  http://www.php.net/manual/en/json.constants.php
     *
     * @param   int $options  json_encode options
     * @throws \Exception
     * @return  string
     */
    public function getEncoded($options = 0)
    {
        if (!is_int($options))
            throw new \Exception('Cannot pass non-int value to getJSON');

        return json_encode($this->json->jsonSerialize(), $options);
    }

    /**
     * Get the unencoded value of the writer
     *
     * @return mixed
     */
    public function getUnencoded()
    {
        return $this->json->getData();
    }

    /**
     * @return JsonObject
     */
    public function getJsonObject()
    {
        return $this->json;
    }

    /**
     * Quick helper function to determine if this object
     * is editable
     *
     * @access  public
     * @return  bool
     */
    protected function canEdit()
    {
        return ($this->started === true && $this->ended === false);
    }

    // ----------------------------

    /**
     * Apply requested encoding type to string
     *
     * @link  http://php.net/manual/en/function.mb-detect-encoding.php
     * @link  http://www.php.net/manual/en/function.mb-convert-encoding.php
     *
     * @param   string $string  un-encoded string
     * @throws \InvalidArgumentException
     * @return  string
     */
    protected function encodeString($string)
    {
        $detect = mb_detect_encoding($string);

        if ($detect === false)
            throw new \InvalidArgumentException('Could not convert string to UTF-8 for JSON output');

        // Just to be safe, attempt to convert all HTML characters to UTF-8 counterparts
        // Borrowed from http://php.net/manual/en/function.html-entity-decode.php#104617
        $string = preg_replace_callback(
            '{(&#[0-9]+;)}',
            function($m) {
                return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
            },
            $string);

        // If the current encoding is already the requested encoding
        if (is_string($detect) && strtolower($detect) === 'utf-8')
            return $string;

        // Else, perform encoding conversion
        return mb_convert_encoding($string, 'UTF-8', $detect);
    }
}
