json-writer-plus
================

Simple JSON Writer library for PHP 5.3+

The goal for this library was to provide a way to construct a JSON object similar in syntax to the PHP <a href="http://www.php.net//manual/en/book.xmlwriter.php" target="_blank">XMLWriter</a>
class.

## Inclusion in your Composer app

Add

```
"dcarbone/json-writer-plus" : "0.1.*"
```

To your application's ``` composer.json ``` file.

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

```php
["Look at all my cool information","My information is the coolest"]

array(2) {
  [0]=>
  string(31) "Look at all my cool information"
  [1]=>
  string(29) "My information is the coolest"
}
```

**Note**: If you pass in an associative array, it will be interpreted into an object as such:

```php
$array = array(
    'Look at all my cool information',
    'property1' => 'property 1 is the coolest property'
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

```php
{"0":"Look at all my cool information","property1":"property 1 is the coolest property"}

object(stdClass)#5 (2) {
  ["0"]=>
  string(31) "Look at all my cool information"
  ["property1"]=>
  string(34) "property 1 is the coolest property"
}
```

You may also perform similar actions with an object via `appendObject($object)`.

## Character Conversion

One of the potentially most frustrating things when working with data from multiple different systems can be character encoding conversion.

I have tried to make this as simple as possible for you to get around, keeping in mind a few things:

* Json data MUST be encoded in UTF-8
* The ability of your system to convert encodings will depend on your specific PHP instance

There are 4 public arrays that are used to help facilitate this:

* **$strSearchCharacters**
* **$strReplaceCharacters**
* **$regexpSearchCharacters**
* **$regexpReplaceCharacters**

### str_ireplace && preg_replace

For a full debrief on these two functions:
* <a href="http://www.php.net//manual/en/function.str-ireplace.php" target="_blank">str_ireplace</a>
* <a href="http://us3.php.net//manual/en/function.preg-replace.php" target="_blank">preg_replace</a>

Every string value that is written to either an object or an array within this library goes through these two functions if the
**xxSearchCharacters** array for the corresponding action contains values.  It is then replaced with the corresponding position **xxReplaceCharacters** value.

### Encoding

After character replacement has occurred, the method `encodeString` is called, and utilizes
PHP's <a href="http://www.php.net//manual/en/function.mb-detect-encoding.php" target="_blank">mb_detect_encoding</a> and <a href="http://www.php.net//manual/en/function.mb-convert-encoding.php" target="_blank">mb_convert_encoding</a> functions.
