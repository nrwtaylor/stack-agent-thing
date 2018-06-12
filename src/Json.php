<?php
namespace Nrwtaylor\StackAgentThing;



//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Json {

	public $var = 'hello';

    function __construct($uuid)
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


        $this->char_max = $this->container['stack']['char_max'];

        $this->write_on_destruct = false;
		// Consider factor this out.  Json should not need to call 
		// Database functions.  Database should do the reading and writing 
		// to the database.  Guess Json needs to be able to trigger
		// a database write though.  

		// This will be creating multiple (unnecessay?) db calls.
        // But needed otherwise readField on null line 422
		$this->db = new Database($uuid, 'refactorout' . $this->mail_postfix);

		$this->array_data = array();
		$this->json_data = '{}';

		$this->field = null;
//        $this->write_field_list = array();
        $this->thing_array= array();
		// Temporary hack.
		$this->uuid = $uuid;

		return;
	}

    function __destruct() {
return;
echo "<pre>";

//echo $this->field;
//echo $this->json_data;

//var_dump($this->thing_array);
//exit();

        foreach($this->thing_array as $field=>$json_data) {
            echo $field;
            echo $json_data;
            $this->db->writeField($field, $json_data);

        }
//exit();
        //if (strtolower($this->field) == 'variables') {

        //$this->db->writeField($this->field, $this->json_data);
        //}

echo "</pre>";
    }

	function time($time = null)
    {
		if ( $time == null ) {$time = time();}
        $this->time = gmdate("Y-m-d\TH:i:s\Z", $time);

        return $this->time;
		//return $this->time = gmdate("Y-m-d\TH:i:s\Z", $time);
	}

    function microtime($time = null)
    {
        if ( $time == null ) {$time = time();}
        //$this->time = gmdate("Y-m-d\TH:i:s.u\Z", $time);


        list($usec, $sec) = explode(' ', microtime());
        //print date('Y-m-d H:i:s', $sec) . $usec;

        $this->microtime = date('Y-m-d H:i:s', $sec) . " " .$usec;

        return $this->microtime;
    }


   	function isUsed()
    {
		// Get latest Thing update from db.
		$thing = $this->getThing();

		// If message field is null, then return false.
		if ($this->thing->$field == null) {
			return false;
		} else {
			return true;
		}

		return;
    }

	function setField($field)
    {
        //$this->write();

		$this->field = $field;
		$this->read();

		return;
    }

    function setArray(Array $array_data)
    {
        $this->array_data = $array_data;
        $this->arraytoJson();
        $this->write();
        return;
    }

    function setJson($json_data)
    {
        $this->json_data = $json_data;
        $this->jsontoArray();
        $this->write();

        return;
    }

//	function streamOn() {
//		$this->field_type = "stream";
//	
//		return;
//		}

//	function streamOff() {
//		$this->field_type = "document";
	
//		return;
//		}

   	function jsontoArray()
    {
		$this->array_data = json_decode($this->json_data, true);
        return;
    }

   	function arraytoJson()
    {
		$this->json_data = json_encode($this->array_data, JSON_PRESERVE_ZERO_FRACTION);
        $this->thing_array[$this->field] = $this->json_data;
        return;
	}

	function idStream()
    {
		if ($this->array_data == null) {
			$this->initField();
			}

		reset($this->array_data);

		$first_key = key($this->array_data);

		if ($first_key == null){
			$this->initField();
			$first_key = key($this->array_data);
		}

		$this->stream_id = $first_key;
		return $first_key;

	}

	function initField()
    {
		// I guess this is appropriate.  A default 'agent' fingers
		// the thing and then identifies posterior associations.
		$arr = array("agent" => array());
		$this->setArray($arr);
		return;
		}

   	function popStream($pos = -1) {

		// pop right by default.

		$stream_id = $this->idStream();
		if ($pos == -1) {
			$pos = count($this->array_data[$stream_id])-1;
		}

		unset($this->array_data[$stream_id][$pos]);

		$this->array_data = array_map('array_values', $this->array_data);
		$this->setArray($this->array_data);

		return;
    }

	function fallingWater($value) {
		// Drop N items off end of queue until less than max_chars.

		// First push onto the left.
		$this->pushStream($value, 0);

		// Check if JSON string too long.

		while (!$this->write()) {
			//echo strlen($this->json_data);
			$this->popStream();
		}

		return;
		}


   	function pushStream($value, $pos = -1) {

		$stream_id = $this->idStream();

		if ($pos == -1) {
			$pos = count($this->array_data[$stream_id]);
		}

		//echo $stream_id;
		array_splice($this->array_data[$stream_id], $pos, 0, $value);
		$this->setArray($this->array_data);

		return;


		}

   	function publishDocument(Array $array_data) {

		return;


		}

   	function deleteVariable(Array $var_path) {
		{
			// we need references as we will modify the first parameter
			$dest = &$this->array_data;
			$finalKey = array_pop($var_path);
			foreach ($var_path as $key) {
				$dest = &$dest[$key];
			}
			unset($dest[$finalKey]);
		}

		$this->arraytoJson();
//		$this->write();
        $this->write();

		return;
		}



   	function readVariable(Array $target_path)
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
		$var_path = $this->recursive_array_search($target_path, $this->array_data);

        // Report with array's match.

        if ($var_path == $target_path) {

//	echo '<pre> json.php readVariable() $var_path: '; print_r($var_path); echo '</pre>';
//	echo '<pre> json.php readVariable() $target_path: '; print_r($target_path); echo '</pre>';
		    $value = $this->getValueFromPath($this->array_data, $var_path);
        } else {
            $value = false;
        }

		return $value;
		}



    function writeVariable(Array $var_path, $value)
    {
        $this->setValueFromPath($this->array_data, $var_path, $value);
        $this->arraytoJson();
        $this->write();
//        $this->write();

        return;
    }





	private function getValueFromPath($arr, $path)
	{
//	echo '<pre> json.php getValueFromPath() $arr: '; print_r($arr); echo '</pre>';
//	echo '<pre> json.php getValueFromPath() $path: '; print_r($path); echo '</pre>';

		// Allow for condition where variable is not found.
		// Consistent with the Thing = false.
		if ($path == false) {return null;}
		if ($arr == false) {return false;}

		// todo: add checks on $path
		$dest = $arr;
		$finalKey = array_pop($path);
		
//		echo "finalkey".$finalKey;

//	echo '<pre> $path: '; print_r($path); echo '</pre>';
		foreach ($path as $key) {
			
		    $dest = $dest[$key];

//	echo '<pre> $dest: '; print_r($dest); echo '</pre>';
		}
		return $dest[$finalKey];
	}

	private function setValueFromPath(&$arr, $path, $value)
	{
		// we need references as we will modify the first parameter
		$dest = &$arr;
		$finalKey = array_pop($path);
		foreach ($path as $key) {
		    $dest = &$dest[$key];
		}

		$dest[$finalKey] = $value;
		return;
	}

	private function recursive_array_search($target_path, $haystack, $var_path = array()) {

//		echo '<pre> json.php recursive_array_search $target_path ';echo print_r($target_path);echo'</pre>';
//		echo '<pre> json.php recursive_array_search $target_path ';echo print_r($var_path);echo'</pre>';

		// Pop off the first value of the array.
		$find = array_shift($target_path);

		foreach($haystack as $key=>$value) {

	        if($key===$find) {

				// Key found add it to the variable path.
				$var_path[] = $key;

				// Next check if it is an array or not
			    if (is_array($value)) {

					// If it is an array, call this function recursively to 
					// explore the next level.

			
					$nextKey = $this->recursive_array_search($target_path, $haystack[$key],$var_path);

					if ($nextKey) {	
						return $nextKey;
					}
				} else {

//					echo $var_path;
//	echo '<pre>var_path a'; print_r($var_path); echo '</pre>';
//$finalKey = array_pop($path);


					return $var_path;
		
				}
			} else {
				//echo "Variable path not found";
			}

	

    }

//		echo '<pre> json.php $var_path ';echo print_r($var_path);echo'</pre>';
//		echo '<pre> json.php $target_path ';echo print_r($target_path);echo'</pre>';
   return $var_path;
}








	function write() 
    {
		// Now write to defined column.
		//print_r($this->json_data);echo "<br>";
		//print_r($this->field);echo "<br>";

        if ($this->field == null) {return;}
        if (strlen($this->json_data) > $this->char_max) {
            //echo $this->json_data;
            echo "Insufficient space available in DB field " . $this->field . " to fully save Thing state.  String length = " . strlen($this->json_data) . " characters.";
            //throw new Exception('Insufficient space in DB record.');
            return false;
        } else {

            //$this->thing_array[$this->field] = $this->json_data;
            if ($this->write_on_destruct) {
                //$this->thing_array[] = array("field"=>$this->field,"data"=>$this->json_data);
                //$this->write_field_list[] = $this->field;
            } else {
                
                $this->db->writeField($this->field, $this->json_data);
            }
            return true;
        }
        return;
    }

    function read()
    {

			$this->json_data = $this->db->readField($this->field);


			if ($this->json_data == null) {$this->initField();}

			$array = $this->jsontoArray();
	$array= $this->array_data;

		return $array;
		}
	

}






?>
