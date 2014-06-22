<?php namespace DCarbone;

use DCarbone\JsonWriterPlus;

/**
 * Class IJsonWritable
 * @package DCarbone
 */
interface IJsonWritable
{
    /**
     * @param JsonWriterPlus $jsonWriter
     * @param IJsonWritable $data
     * @return mixed
     */
    public function buildJson(JsonWriterPlus &$jsonWriter, IJsonWritable &$data = null);
}