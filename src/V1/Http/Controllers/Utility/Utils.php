<?php

namespace AspireRESTAPI\V1\Http\Controllers\Utility;
use Illuminate\Support\Facades\DB;

class Utils
{    
    /**
     * Method to print any output in pretty way
     * @param mixed
     * @return void
     */
    public static function printData($obj, $dump = false)
    {
        echo "<pre>";
        if ($dump === true) {
            var_dump($obj);
        } else {
            print_r($obj);
        }
        echo "</pre>";
    }

    /**
     * Get Document Root
     *
     * @return string
     */
    public static function getDocumentRoot(): string
    {
        $docRoot = dirname(__DIR__).'/';

        if (php_sapi_name() !== 'cli') {
            $docRoot = static::getServerHeader('DOCUMENT_ROOT');
        }

        return $docRoot;
    }

    /**
     * Convert input into Json Object
     * @param mixed $json
     * @param int $options
     * @return string
     */
    public static function json($json, $options = 0)
    {
        if (is_array($json)) {
            return json_encode($json, $options);
        }
        return json_encode(array($json), $options);
    }

    /**
     * Convert Json Object into array or \StdClass
     * @param mixed
     * @param bool
     * @return array|object|null
     */
    public static function jsonDecode($json = null, $option = false)
    {
        if (null === $json) {
            return $json;
        }

        return json_decode($json, $option);
    }
}
