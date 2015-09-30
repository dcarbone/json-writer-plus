json-writer-plus
================

Simple JSON Writer library for PHP 5.3+

The goal for this library was to provide a way to construct a JSON object similar in syntax to the PHP <a href="http://www.php.net//manual/en/book.xmlwriter.php" target="_blank">XMLWriter</a>
class.

## Inclusion in your Composer app

Add

```json
"dcarbone/json-writer-plus" : "0.2.*"
```

To your application's `composer.json` file.

Learn more about Composer here: <a href="https://getcomposer.org/">https://getcomposer.org/</a>


## Basic usage

To get started creating your own JSON object:

```php

use \DCarbone\JsonWriterPlus;

// Create instance
$jsonWriter = new JsonWriterPlus();

// Start the writer object
$jsonWriter->startJson();

// Open a new object for population
$jsonWriter->writeStartObject();

// Directly write a property name and value to the opened object
$jsonWriter->writeObjectProperty('PropertyKey', 'PropertyValue');

// Write a new property name for later population
$jsonWriter->writeObjectPropertyName('ValueArray');

// Start an array.  Since we wrote a new property name above, it is automatically appended
// to the parent object at the previously specified key
$jsonWriter->writeStartArray();

// Add two values to the array
$jsonWriter->writeValue("Value1");
$jsonWriter->writeValue("Value2");

// Close the array
$jsonWriter->writeEndArray();

// Close the parent object
$jsonWriter->writeEndObject();

// Close the writer
$jsonWriter->endJson();

// See the "jsonized" version of the above actions
echo $jsonWriter->getJsonString()."\n";

// See the internal representation of the above actions as PHP sees it
echo '<pre>';
var_dump($jsonWriter->getUnencoded());
echo '</pre>';

```

The above code block will result in the following being output:

```php
{"PropertyKey":"PropertyValue","ValueArray":["Value1","Value2"]}

object(stdClass)#4 (2) {
  ["PropertyKey"]=>
  string(13) "PropertyValue"
  ["ValueArray"]=>
  array(2) {
    [0]=>
    string(6) "Value1"
    [1]=>
    string(6) "Value2"
  }
}
```

## Starting out

The above example demonstrates a Json output with the primary element being an Object, but you may also start things out with an array:

```php
// Initialize writer
$jsonWriter = new JsonWriterPlus();

// Start writer
$jsonWriter->startJson();

// Open root array
$jsonWriter->writeStartArray();

// Open object as first item of root array
$jsonWriter->writeStartObject();
$jsonWriter->writeObjectProperty('Property1', 'This object is inside an array!');
$jsonWriter->writeEndObject();

// Open new array as 2nd item of root array
$jsonWriter->writeStartArray();
$jsonWriter->writeValue('Nested array value 1');
$jsonWriter->writeValue('Nested array value 2');
$jsonWriter->writeEndArray();

// Write a string value directly to root array as 3rd item
$jsonWriter->writeValue('Root array value');

$jsonWriter->writeEndArray();

$jsonWriter->endJson();

echo $jsonWriter->getJsonString()."\n";
echo '<pre>';
var_dump($jsonWriter->getUnencoded());
echo '</pre>';
```

The above will output:

```php
[{"Property1":"This object is inside an array!"},["Nested array value 1","Nested array value 2"],"Root array value"]

array(3) {
  [0]=>
  object(stdClass)#4 (1) {
    ["Property1"]=>
    string(31) "This object is inside an array!"
  }
  [1]=>
  array(2) {
    [0]=>
    string(20) "Nested array value 1"
    [1]=>
    string(20) "Nested array value 2"
  }
  [2]=>
  string(16) "Root array value"
}

```

## Fun stuff

Lets say you have a JsonWriter instance already open and an array already constructed, and you wish to just append the entire thing
to the Json output without looping through and manually performing actions.  Well, good sir/ma'am/fish, you can!

```php
$array = array(
    'Look at all my cool information',
    'My information is the coolest'
);

$jsonWriter = new JsonWriterPlus();

$jsonWriter->startJson();

$jsonWriter->appendArray($array);

$jsonWriter->endJson();

echo $jsonWriter->getJsonString()."\n";
echo '<pre>';
var_dump($jsonWriter->getUnencoded());
echo '</pre>';
```

The above will output:

```json
["Look at all my cool information","My information is the coolest"]
```
```php
array(2) {
  [0]=>
  string(31) "Look at all my cool information"
  [1]=>
  string(29) "My information is the coolest"
}
```

```php
$array = array(
    'Look at all my cool information',
    'property1' => 'property 1 is the coolest property',
    'property2' => 'property2 is the next coolest property',
    'this is also cool information'
);

$jsonWriter = new JsonWriterPlus();

$jsonWriter->startJson();

$jsonWriter->appendArray($array);

$jsonWriter->endJson();

echo $jsonWriter->getJsonString()."\n";
echo '<pre>';
var_dump($jsonWriter->getUnencoded());
echo '</pre>';
```

Will result in:

```json
["Look at all my cool information",{"property1":"property 1 is the coolest property"},{"property2":"property2 is the next coolest property"},"this is also cool information"]
```

```php
array(4) {
  [0]=>
  string(31) "Look at all my cool information"
  [1]=>
  object(stdClass)#4 (1) {
    ["property1"]=>
    string(34) "property 1 is the coolest property"
  }
  [2]=>
  object(stdClass)#5 (1) {
    ["property2"]=>
    string(38) "property2 is the next coolest property"
  }
  [3]=>
  string(29) "this is also cool information"
}
```

You may also perform similar actions with an object via `appendObject($object)`.
