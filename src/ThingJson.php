<?php
/**
 * Json.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

ini_set("allow_url_fopen", 1);

class ThingJson
{
    public $var = 'hello';

    /**
     *
     * @param unknown $uuid
     * @return unknown
     */
    function __construct($thing = null, $text = null)
    {
        $this->start_time = microtime(true);
        //        $settings = require 'settings.php';
        $settings = require $GLOBALS['stack_path'] . "private/settings.php";
        $this->container = new \Slim\Container($settings);

        $this->mail_postfix = $settings['settings']['stack']['mail_postfix'];

        $this->container['stack'] = function ($c) {
            $db = $c['settings']['stack'];
            return $db;
        };

        $this->size_overflow = false;
        $this->write_fail_count = 0;

        $this->char_max = $this->container['stack']['char_max'];

        $this->write_on_destruct = false;

        // This is a useful function to maintain an in-sync
        // PHP Array and JSON text pair of variables.

        $this->array_data = [];
        $this->json_data = '{}';

        $this->field = null;
        //        $this->write_field_list = array();
//        $this->thing_array = [];
        // Temporary hack of sorts.
        // $this->uuid = $uuid;
        //$this->uuid = $thing->uuid;
    }

    /**
     *
     * @param unknown $method
     * @param unknown $args
     */
    function __call($method, $args)
    {
    }

    /**
     *
     */
    function __destruct()
    {
    }

    /**
     *
     * @return unknown
     */
    function isUsed()
    {
        // Get latest Thing update from db.
        $thing = $this->getThing();

        // If message field is null, then return false.
        if ($this->thing->$field == null) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param unknown $field
     */
    function setField($field)
    {
        $this->field = $field;
        //$this->read();
    }

    /**
     *
     * @param array   $array_data (optional)
     */
    function setArray(array $array_data = null)
    {
        if ($array_data == null) {
            $array_data = $this->array_data;
        }
        $this->array_data = $array_data;
/*
        foreach ($array_data as $key => $value) {
            $this->{$key} = $value;
        }
*/
        $this->arraytoJson();
        //$this->write();
    }

    /**
     *
     * @param unknown $json_data
     */
    function setJson($json_data)
    {
        $this->json_data = $json_data;
        $this->jsontoArray();
        //$this->write();
    }

    public function jsontoarrayJson($json_data = null)
    {
        return $this->jsontoArray($json_data);
    }

    /**
     *
     * @param unknown $json_data (optional)
     * @return unknown
     */
    public function jsontoArray($json_data = null)
    {
        if ($json_data == null) {
            $json_data = $this->json_data;
        }

        $array_data = json_decode($json_data, true);

        if ($array_data == false) {
            $this->array_data = false;
            return;
        }

        if (is_string($array_data)) {
            $array_data = ['text' => $array_data];
        }

        if (is_array($array_data)) {

/*
            foreach ($array_data as $key => $value) {
                if ($key != "") {
                    $this->{$key} = $value;
                }
            }
*/
        }

        $this->array_data = $array_data;

        return $array_data;
    }

    /**
     *
     */
    function arraytoJson($array_data = null)
    {
        if ($array_data == null) {
            $array_data = $this->array_data;
        }
        $this->json_data = json_encode(
            $array_data,
            JSON_PRESERVE_ZERO_FRACTION
        );
  //      $this->thing_array[$this->field] = $this->json_data;
        return $this->json_data;
    }

    /**
     *
     * @param unknown $stream_text (optional)
     * @return unknown
     */
    function idStream($stream_text = null)
    {
        if ($stream_text != null) {
            $this->stream_id = $stream_text;
        }

        if ($this->array_data == null) {
            $this->initField();
        }

        // Set point to first element
        reset($this->array_data);

        $first_key = key($this->array_data);
        if ($first_key == null) {
            $this->initField();
            $first_key = key($this->array_data);
        }

        $this->stream_id = $first_key;
        return $first_key;
    }

    /**
     *
     */
    function initField()
    {
        // I guess this is appropriate.  A default 'agent' fingers
        // the thing and then identifies posterior associations.
        $arr = ["agent" => []];
        $this->setArray($arr);
    }

    /**
     *
     * @param unknown $pos (optional)
     */
    function popStream($pos = -1)
    {
        // pop right by default.

        $stream_id = $this->idStream();
        if ($pos == -1) {
            $pos = count($this->array_data[$stream_id]) - 1;
        }

        unset($this->array_data[$stream_id][$pos]);

        $this->array_data = array_map('array_values', $this->array_data);
        $this->setArray($this->array_data);
    }

    /**
     *
     * @param unknown $value
     */
    function fallingWater($value)
    {
        // Drop N items off end of queue until less than max_chars.

        // First push onto the left.
        $response = $this->pushStream($value, 0);

        if ($response != true) {
            echo "fallingWater";
            return;
        }
        // Check if JSON string too long.
        if ($response == true) {
            // Failed to push
            $this->popStream();
            $this->popStream();
        }

        $response = $this->pushStream($value, 0);

        if ($response == true) {
            // Failed to push
            $this->popStream();
            $this->popStream();
        }

        $this->pushStream($value, 0);
    }

    /**
     *
     * @param unknown $value
     * @param unknown $pos   (optional)
     */
    function pushStream($value, $pos = -1)
    {
        // dev
        //if ($this->array_data == null) {return;}

        $this->setField($this->field);

        $stream_id = $this->idStream();
        if ($this->array_data[$stream_id] == null) {
            return true;
        }

        if ($pos == -1) {
            $pos = count($this->array_data[$stream_id]);
        }
        array_splice($this->array_data[$stream_id], $pos, 0, $value);
        $this->setArray($this->array_data);
        return null;
    }

    /**
     *
     * @param array   $array_data
     */
    function publishDocument(array $array_data)
    {
    }

    /**
     *
     * @param array   $var_path
     */
    function deleteVariable(array $var_path)
    {
        // we need references as we will modify the first parameter
        $dest = &$this->array_data;
        $finalKey = array_pop($var_path);
        foreach ($var_path as $key) {
            $dest = &$dest[$key];
        }
        unset($dest[$finalKey]);

        $this->arraytoJson();
        //$this->write();
    }

    /**
     *
     * @param array   $var_path
     * @param unknown $value
     */
    function writeVariable(array $var_path, $value)
    {
        //        $array_data = $this->array_data;

        $this->setValueFromPath($this->array_data, $var_path, $value);
        $this->arraytoJson();
        //$t = $this->write();

        // Failing to write a variable isn't a problem.
        // The agents will do what they can.

        //        if ($t === false) {throw new \Exception("Stack write failed.");}
        $this->size_overflow = false;
        /*
        if ($t === false) {
            $this->size_overflow = strlen($this->json_data) - $this->char_max;
            $this->write_fail_count += 1;
            $t = new Thing(null);
            $t->Create("x", "y", "s/ error");
            $a = new Hey($t);
        }
*/
    }

    /**
     *
     * @param unknown $arr
     * @param unknown $path
     * @return unknown
     */
    private function getValueFromPath($arr, $path)
    {
        // Allow for condition where variable is not found.
        // Consistent with the Thing = false.
        if ($path == false) {
            return null;
        }
        if ($arr == false) {
            return false;
        }

        // todo: add checks on $path
        $dest = $arr;
        $finalKey = array_pop($path);

        foreach ($path as $key) {
            $dest = $dest[$key];
        }
        return $dest[$finalKey];
    }

    /**
     *
     * @param unknown $arr   (reference)
     * @param unknown $path
     * @param unknown $value
     */
    private function setValueFromPath(&$arr, $path, $value)
    {
        if (!is_array($arr)) {
            return true;
        }
        // we need references as we will modify the first parameter
        $dest = &$arr;
        if ($dest == null) {
            $dest = [];
        }
        //var_dump($dest);
        //return null;}
        $finalKey = array_pop($path);
        foreach ($path as $key) {
            $dest = &$dest[$key];
        }

        if (is_array($finalKey)) {
            throw new Exception('Array received as path.');
            return true;
        }
        if (is_string($dest)) {
            return true;
            // dev 5 November 2021
        }
        $dest[$finalKey] = $value;
    }

    /**
     *
     * @param unknown $target_path
     * @param unknown $haystack
     * @param unknown $var_path    (optional)
     * @return unknown
     */
    private function recursive_array_search(
        $target_path,
        $haystack,
        $var_path = []
    ) {
        // Pop off the first value of the array.
        $find = array_shift($target_path);

        foreach ($haystack as $key => $value) {
            if ($key === $find) {
                // Key found add it to the variable path.
                $var_path[] = $key;

                // Next check if it is an array or not
                if (is_array($value)) {
                    // If it is an array, call this function recursively to
                    // explore the next level.

                    $nextKey = $this->recursive_array_search(
                        $target_path,
                        $haystack[$key],
                        $var_path
                    );

                    if ($nextKey) {
                        return $nextKey;
                    }
                } else {
                    return $var_path;
                }
            } else {
            }
        }
        return $var_path;
    }

    function read()
    {
        var_dump("ThingJson read called - no action");
    }

    function write()
    {
        var_dump("ThingJson write called - no action");
    }
}
