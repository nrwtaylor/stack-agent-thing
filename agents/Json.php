<?php
/**
 * Json.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);

class Json extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param unknown $uuid
     * @return unknown
     */
public function init()
    {
        $this->start_time = microtime(true);
        //        $settings = require 'settings.php';
     //   $settings = require($GLOBALS['stack_path'] . "private/settings.php");
   //     $this->container = new \Slim\Container($settings);

//        $this->mail_postfix = $settings['settings']['stack']['mail_postfix'];

  //      $this->container['stack'] = function ($c) {
    //        $db = $c['settings']['stack'];
      //      return $db;
//        };

        $this->size_overflow = false;
        $this->write_fail_count = 0;

        //$this->char_max = $this->container['stack']['char_max'];
        $char_max_default = 4000;
        $this->char_max = $this->settingsAgent(["stack", "char_max"], $char_max_default);



        $this->write_on_destruct = false;

        // Consider factor this out.  Json should not need to call
        // Database functions.  Database should do the reading and writing
        // to the database.

        // Guess Json needs to be able to trigger
        // a database write though.

        // This will be creating multiple (unnecessay?) db calls.
        // But needed otherwise readField on null line 422
        // $this->db = new Database(null, ['uuid'=>$uuid, 'from'=>'refactorout' . $this->mail_postfix]);

        // new Database(false, ...) creates a read-only thing.
        $this->db = new Database($thing, ['uuid'=>$uuid, 'from'=>'refactorout' . $this->mail_postfix]);

        $this->array_data = array();
        $this->json_data = '{}';

        $this->field = null;
        //        $this->write_field_list = array();
        $this->thing_array = array();
        // Temporary hack of sorts.
        $this->uuid = $uuid;
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
     * @param unknown $time (optional)
     * @return unknown
     */
    function deprecate_time($time = null)
    {
        if ($time == null) {
            $time = time();
        }
        $this->time = gmdate("Y-m-d\TH:i:s\Z", $time);

        return $this->time;
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
        $this->read();
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

        foreach ($array_data as $key => $value) {
            $this->{$key} = $value;
        }
        $this->arraytoJson();
        $this->write();
    }

    /**
     *
     * @param unknown $json_data
     */
    function setJson($json_data)
    {
        $this->json_data = $json_data;
        $this->jsontoArray();
        $this->write();
    }

    public function jsontoarrayJson($json_data = null) {
       return $this->jsontoArray($json_data);
    }

    /**
     *
     * @param unknown $json_data (optional)
     * @return unknown
     */
    public function jsontoArray($json_data = null)
    {
        var_dump("Json jsontoArray called");
        if ($json_data == null) {
            if ( (isset($this->json_data)) and (is_array($this->json_data)) ) {
            $json_data = $this->json_data;
            }
        }

        $array_data = json_decode($json_data, true);

        if ($array_data == false) {
            $this->array_data = false;
            return false;
        }

        if (is_string($array_data)) {
            $array_data = ['text'=>$array_data];
        }


        foreach ($array_data as $key => $value) {
            if ($key != "") {
                $this->{$key} = $value;
            }
        }

        $this->array_data = $array_data;

        return $array_data;
    }

    public function arrayJson($arr) {

        $json_data = json_encode(
            $arr,
            JSON_PRESERVE_ZERO_FRACTION
        );
        return $json_data;

    }

    /**
     *
     */
    function arraytoJson()
    {
        $this->json_data = json_encode(
            $this->array_data,
            JSON_PRESERVE_ZERO_FRACTION
        );
        $this->thing_array[$this->field] = $this->json_data;
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
        $arr = array("agent" => array());
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
        $this->pushStream($value, 0);

        if ($this->db->last_update != true) {
            echo "fallingWater";
            return;
        }
        // Check if JSON string too long.
        if ($this->db->last_update == true) {
            // Failed to push
            $this->popStream();
            $this->popStream();
        }

        $this->pushStream($value, 0);

        if ($this->db->last_update == true) {
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
if ($this->array_data[$stream_id] == null) {return;}

        if ($pos == -1) {
            $pos = count($this->array_data[$stream_id]);
        }
        array_splice($this->array_data[$stream_id], $pos, 0, $value);
        $this->setArray($this->array_data);
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
        $this->write();
    }

    /**
     *
     * @param array   $target_path
     * @return unknown
     */
    function readVariable(array $target_path)
    {
        // See if this helps.

        $this->jsontoArray();

        // Returns false if variable not found.
        //$this->rec_array_replace($var_path, $value, $this->array_data);

        // Here a recursive array search is required because of the
        // ambiguity that the value can also be a key.
        // A key building pattern (using the get/setValuefromPath doesn't
        // accomodate this.

        // So here do a search for each element of the target_path
        // regardless whether the 'key' or 'value' matches.
        // Return the path.

        if ($this->array_data == false) {
            return false;
        }

        $var_path = $this->recursive_array_search(
            $target_path,
            $this->array_data
        );

        // Report with array's match.

        if ($var_path == $target_path) {
            $value = $this->getValueFromPath($this->array_data, $var_path);
        } else {
            $value = false;
        }

        return $value;
    }

    /**
     *
     * @param array   $var_path
     * @param unknown $value
     */
    function writeVariable(array $var_path, $value)
    {
        $this->setValueFromPath($this->array_data, $var_path, $value);
        $this->arraytoJson();
        $t = $this->write();

        // Failing to write a variable isn't a problem.
        // The agents will do what they can.

        //        if ($t === false) {throw new \Exception("Stack write failed.");}
        $this->size_overflow = false;
        if ($t === false) {
            $this->size_overflow = strlen($this->json_data) - $this->char_max;
            $this->write_fail_count += 1;
            $t = new Thing(null);
            $t->Create("x", "y", "s/ error");
            $a = new Hey($t);
        }
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
        // we need references as we will modify the first parameter
        $dest = &$arr;

if ($dest == null) {
$dest =[];
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
        $var_path = array()
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

    /**
     *
     * @return unknown
     */
    function write()
    {
        // Now write to defined column.
        if ($this->field == null) {
            return;
        }
        if (strlen($this->json_data) > $this->char_max) {

            // devstack what do you do here?
            // This is the place where Json borks when asked to save too much.

            // Clearly expected behaviour and the agents should be aware.
            // Rather not raise an exception for something so routine.

            // Rely on agents to check if it's necessary to flag an exception.

            // Nah. Do the hard work through exceptions.

            $thing = new Thing(null);
            $thing->Create(
                null,
                "human",
                'Stack variables size exceeded ' . $this->uuid . " writing to field " . $this->field . "."
            );
            $thing_agent = new Hey($thing);

            $this->last_write = true;

            //            throw new \Error('Insufficient space in DB record ' . $this->uuid . ".");
            throw new \OverflowException(
                'Overflow: Insufficient space in DB record ' . $this->uuid . " writing to field " . $this->field . "."
            );
            //            $this->overload_length = strlen($this->json_data) - $this->char_max;

            return false;
        } else {
var_dump("Json Write pre-write");

            //$this->thing_array[$this->field] = $this->json_data;
            if ($this->write_on_destruct) {
                //$this->thing_array[] = array("field"=>$this->field,"data"=>$this->json_data);
                //$this->write_field_list[] = $this->field;
            } else {
/*
                $this->last_write = $this->db->writeDatabase(
                    $this->field,
                    $this->json_data
                );
*/
                $this->last_write = $this->db->writeDatabase(
                    $this->field,
                    $this->array_data
                );


            }
var_dump("Json Write performed");
            return true;
        }
        return;
    }

    /**
     *
     * @return unknown
     */
    function read($variable = null)
    {
$array = null;

if ((isset($this->db)) and ($this->db != null)) {
        $this->json_data = $this->db->readField($this->field);

        // Whitefox introduces setting json data.
        // Also set array data and test.
        $this->array_data = $this->json_data;

        //        if ($this->json_data == null) {$this->initField();}
        //$array = $this->jsontoArray();
        $array = $this->array_data;
}
        return $array;
    }
}
