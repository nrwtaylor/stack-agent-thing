<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Resource
{

    // This is a resource.

    // A resource for this machine is piles and streams of text.

    // And recognizing which pieces of text are valuable.
    // Responds (devstack) to "resource is".
    // And (devstack) nextResource.

    // This is an agent of a place.  They can probaby do a lot for somebody.
    // With the right questions.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) 
    {

        $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_prefix = 'Agent "Place" ';

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");

        // I'm not sure quite what the node_list means yet
        // in the context of headcodes.
        // At the moment it seems to be the headcode routing.
        // Which is leading to me to question whether "is"
        // or "Place" is the next Agent to code up.  I think
        // it will be "Is" because you have to define what 
        // a "Place [is]".
 //       $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
 //       $this->thing->choice->load('headcode');

        $this->keywords = array('resource','next', 'accept', 'clear', 'drop','add','new','here','there');



        $this->default_resource_name = $this->thing->container['api']['resource']['default_resource_name'];
        $this->default_resource_quantity = $this->thing->container['api']['resource']['default_resource_quantity'];

        $this->default_resource_quantity = "Z";

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->json->time();

		$this->test= "Development code"; // Always iterative.

        // Non-nominal
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;

        // Potentially nominal
        $this->subject = $thing->subject;

        // Treat as nominal
        $this->from = $thing->from;

        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->state = null; // to avoid error messages

//        $this->findResource("Geographical Name","New Westminster");


        // Read the subject to determine intent.

		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();
        if ($this->agent_input == null) {
		    $this->respond();
        }

        if ($this->agent_input != "extract") {
            $this->set();
        }

        $this->thing->log( $this->agent_prefix .' loaded resource_name ' . $this->resource_name . " and resource_quantity " . $this->resource_quantity . "." );


//		$this->thing->log('<pre> Agent "Headcode" completed</pre>');
        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;
    }



    function nextResource($file_name, $selector_array = null)
    {
        //if ($file_name == null) {
            $resource_name = "resources/places_canada/cgn_bc_csv_eng.csv";
        //}


        //$this->thing->log("nextGtfs " . $file_name . " ");
        $split_time = $this->thing->elapsed_runtime();

//        $file = $GLOBALS['stack_path'] . 'resources/translink/' . $file_name . '.txt';
        $file = $GLOBALS['stack_path'] . $resource_name;


        $handle = fopen($file, "r");
        $line_number = 0;
       while(!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;
            //echo ".";
            // Get headers
            if ($line_number == 1) {
                $i = 0;
                $field_names = explode(",",$line);

                foreach ($field_names as $field){ 
                    $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                    $i += 1;
                }
                continue;
            }


            //$line = trim(fgets($handle));
            $arr = array();
            $field_values = explode(",",$line);
            $i = 0;
            foreach($field_names as $field_name) {
                if (!isset($field_values[$i])) {$field_values[$i] = null;}
                $arr[$field_name] = $field_values[$i];
                $i += 1;
            }

            // If there is no selector array, just return it.
            if ($selector_array == null) {yield $arr;continue;}

            if (array_key_exists(0,$selector_array)) {
            } else {
                 $selector_array = array($selector_array);
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name=>$field_value) {

                //if ($selector_array == null) {$matches[] = $iteration; continue;}

                // Look for all items in the selector_array matching
                if ($selector_array == null) {continue;}

                foreach ($selector_array as $selector) {
                    //var_dump($selector_array);

                    foreach ($selector as $selector_name=>$selector_value) {

                        if ($selector_name != $field_name) {continue;}

                        if ($selector_value == $field_value) {

                            $match_count += 1;
                        } else {
                            $match = false; 
                            break;
                        }
                    }
                }
            }

            if ($match == false) {continue;}

            yield $arr;

        }

        fclose($handle);

        $this->thing->log('nextGtfs took ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');



    }


    function getNumber()
    {
//        require_once '/var/www/html/stackr.ca/agents/number.php';
        $agent = new Number($this->thing, "number");

        $numbers = $agent->numbers;

        if (count($numbers) == 1) {$this->resource_quantity = $numbers[0];
        } else {
            $this->resource_quantity = "X";
        }


    }

    function set()
    {
//$this->head_code = "0Z15";
        $resource = new Variables($this->thing, "variables resource " . $this->from);

        $resource->setVariable("resource_quantity", $this->resource_quantity);
        $resource->setVariable("resource_name", $this->resource_name);
     //   $this->headcode->setVariable("index", $this->index);
        $resource->setVariable("refreshed_at", $this->current_time);

$this->thing->log( $this->agent_prefix .' set ' . $this->resource_quantity . ' and ' . $this->resource_name . ".", "INFORMATION" );


        //$this->thing->json->writeVariable( array("headcode", "head_code"), $this->head_code );
        //$this->thing->json->writeVariable( array("headcode", "refreshed_at"), $this->current_time );

        $place = new Variables($this->thing, "variables " . $this->resource_name . " " . $this->from);

        //$this->head_code = $this->headcode->getVariable("head_code");
        //$this->headcode->setVariable("consist", $this->consist);
        //$this->headcode->setVariable("run_at", $this->run_at);
        //$this->headcode->setVariable("quantity", $this->quantity);
        $place->setVariable("resource_name", $this->resource_name);
        $place->setVariable("resource_quantity", $this->resource_quantity);


        return;
    }


    function nextPlace() {

        $this->thing->log("next place");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.

        return $this->available;

    }

    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

    function findResource($category = null, $value = null)
    {


        if ($value == null) {
            $value = "Vancouver"; // Largest population center ... see what resources there are
        }

        if ($category == null) {
            $category = "Geographical Name";
        }
//var_dump($category);
//var_dump($value);
        // Is this find?

        //$selector_array = array(array("stop_id"=>$station_id_input));
        $selector_array = array($category=>$value);

        $this->resources = array();
        for ($resources = $this->nextResource("meep", $selector_array); $resources->valid(); $resources->next()) {

            $resource = $resources->current();
            $id = $resource['CGNDB ID'];


            $code = $resource['Concise Code'];
            $description = $resource['Generic Term'];

            $latitude = $resource['Latitude'];
            $longitude = $resource['Longitude'];
            $name = $resource['Geographical Name'];

            $resource = array("name"=>$name,
                                "code"=>$code,
                                "latitude"=>$latitude,
                                "longitude"=>$longitude,
                                "description"=>$description,
                                "quantity"=>1,
                                "id"=>$id);

            // Not sure about using BC place code to identify resource
            // Decided id is better
            $this->resources[$name][$id] = $resource;

        }



    }

    function getResource($selector = null)
    {
        foreach ($this->resources as $resource) {
            // so this is where it doesn't do anything useful.
            // need to get places returning known relevant places

            if (($resource['name'] == $selector)) {
//var_dump($resource);
                $this->resource_name = $resource['name'];
                $this->resource_quantity = $resource['quantity'];
                $this->place = new Variables($this->thing, "variables " . $this->resource_name . " " . $this->from);

                return array($this->resource_name, $this->resource_quantity);
            }

       }

       return true;

    }

    function getResources() {

        //$this->placecode_list = array();
        $this->resourcename_list = array();
        $this->resources = array();
        // See if a headcode record exists.
//        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new Findagent($this->thing, 'resource');

        $this->thing->log('Agent "Place" found ' . count($findagent_thing->thing_report['things']) ." resource Things." );

//        if ($findagent_thing->thing_reports['things'] == false) {
//                $place_code = $this->default_place_code;
//                $place_name = $this->default_place_name;
//            return array($this->placecode_list, $this->placename_list, $this->places);
//        }

    if ( ($findagent_thing->thing_report['things'] == true)) {}

    if ((count($findagent_thing->thing_report['things']) == 0) or 
        ($findagent_thing->thing_report['things'] == 0)) {
        // No places found
    } else {
        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            // refactor to avoid unnecessary Thing
            $thing= new Thing($uuid);
            $variables = $thing->account['stack']->json->array_data;

            if (isset($variables['place'])) {

                $resource_quantity = $this->default_resource_quantity;
                $resource_name = $this->default_resource_name;

                if(isset($variables['resource']['resource_quantity'])) {$resource_quantity = $variables['resource']['resource_quantity'];}
                if(isset($variables['resource']['resource_name'])) {$resource_name = $variables['resource']['resource_name'];}

                $this->resources[$resource_name] = array("quantity"=>$resource_quantity, "name"=>$resource_name);
                //$variables['place'][] = $thing_object['task'];
          //      $this->placecode_list[] = $place_code;
                $this->resourcename_list[] = $resource_name;

            }
        }

}

        // Add in a set of default places

        $default_resourcename_list = array("Fuel","Food","Water","Communications");

        foreach ($default_resourcename_list as $resource_name) {
                //$place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

                //$this->placecode_list[] = $place_code;
                $this->resourcename_list[] = $resource_name;
                $this->resources[] = array("quantity"=>$resource_quantity, "name"=>$resource_name); 
       }

       // Indexing not implemented
        $this->max_index = 0;


        return array($this->resourcename_list, $this->resources);

    }

    private function get($place_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($place_code == null) {$place_code = $this->place_code;}

        $this->place = new Variables($this->thing, "variables " . $resource_name . " " . $this->from);

        $this->resource_quantity = $this->place->getVariable("resource_quantity");
        $this->resource_name = $this->place->getVariable("resource_name");

        return array($this->resource_quantity, $this->resource_name);
    }

    function dropResource() {
        $this->thing->log($this->agent_prefix . "was asked to drop a Place.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->resource)) {
            $this->resource->Forget();
            $this->resource = null;
        }

        $this->get();
 
    }


    function makeResource($resource_name = null) {

//        if (($place_code == null) or ($place_name == null)) {return true;}
        if ($resource_name == null) {return true;}


//        if ($place_code == null) {$place_code = $this->nextCode();}


        // See if the code or name already exists
        foreach ($this->resources as $resource) {

            if ($resource_name == $resource['name']) {return true;}
        }



        // Will be useful when devstack makePlace
        //$place_name = $this->getVariable('place_name', $place_name);


        $this->thing->log('Agent "Resource" will make a Resource for ' . $resource_name . ".");


        $ad_hoc = true;
        echo "ready to make a resource<br>";
        if ( ($ad_hoc != false) ) {
            echo "making a resource";
            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $this->default_resource_quantity = "X";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log($this->agent_prefix . "was told the Resource is Useable but we might lose it.");

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if (!isset($this->resource_quantity)) {
                $this->resource_quantity = $this->default_resource_quantity;

            }

            $this->current_resource_name = $resource_name;
            $this->resource_name = $resource_name;

            $this->set();

            $this->getResources();
            $this->getResource($this->resource_name);

var_dump($this->resource_name);

            $this->resource_thing = $this->thing;

        }

//var_dump($this->place_code);
//var_dump($this->place_name);
//exit();
        // Need to code in the X and <number> conditions for creating new headcodes.

        // Write the variables to the db.
        //$this->set();

        //$this->headcode_thing = $this->thing;

        $this->thing->log('Agent "Resource" found a Place and pointed to it.');

    }

    function resourceTime($input = null) {

        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $resource_time = "x";
            return $resource_time;
        }


        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $resource_time = $this->hour . $this->minute;

        if ($input == null) {$this->resource_time = $resource_time;}

        return $resource_time;



    }

    public function extractResources($input = null) {


        if (!isset($this->resource_names)) {
            $this->resource_names = array();
        }

        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        //$pattern = "|\d{6}$|";

        //preg_match_all($pattern, $input, $m);
        //$this->place_codes = $m[0];


        // Look for an established list of places.
        //$default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        //if (!isset($this->place_name_list)) {$this->get();}

        //$this->place_names = array();
        //foreach ($places as $place) {

            if (!isset($this->resources)) {$this->getResources();}

            foreach ($this->resources as $resource) {

                $resource_name = strtolower($resource['name']);
                $resource_quantity = strtolower($resource['quantity']);

//if ($place_name == null) {continue;}
//if ($place_code == null) {continue;}
//exit();
                // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word

                if (strpos($input, $resource_name) !== false)  {
                    $this->resource_names[] = $resource_name;
                }


            }



        //}

$this->resource_names = array_unique($this->resource_names);


        return $this->resource_names;
    }

    public function extractResource($input)
    {
        $this->resource_name = null;

        $resource_names = $this->extractResources($input);

        if ( count($resource_names) == 1) {
            if (isset($resource_names[0])) {$this->resource_name = $resource_names[0];}

            $this->thing->log( $this->agent_prefix  . 'found a resource name (' . $this->resource_name . ') in the text.');
            return $this->resource_name;
        }


        //if (count($place_codes == 0)) {return false;}
        //if (count($place_codes > 1)) {return true;}

        // And then extract place names.
        // Take out word 'place' at the start.
//        $filtered_input = ltrim(strtolower($input), "place");


//echo $filtered_input;
//exit();


        if (count($resource_names) == 1) {$this->resource_name = $this->resource_names[0];}
//var_dump($place_names);
        return $this->resource_name;
    }


    function assertResource($input) {


if (($pos = strpos(strtolower($input), "resource is")) !== FALSE) { 
    $whatIWant = substr(strtolower($input), $pos+strlen("resource is")); 
} elseif (($pos = strpos(strtolower($input), "resource")) !== FALSE) { 
    $whatIWant = substr(strtolower($input), $pos+strlen("resource")); 
}

$filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($this->getResource($filtered_input)) {
         //true so make a place
            $this->makeResource($filtered_input);
        }


    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        //return $this->available;
    }



    function addResource() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    function makeTXT()
    {
        if (!isset($this->placecode_list)) {$this->getResources();}

        $this->getResources();

        if (!isset($this->resource)) {$txt = "Not here";} else {

        $txt = 'These are RESOURCES for RAILWAY ' . $this->resource->nuuid . '. ';
        }
        $txt .= "\n";
//        $txt .= count($this->placecode_list). ' Place codes and names retrieved.';

        $txt .= "\n";


        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("QUANTITY", 8, " ", STR_PAD_LEFT);


        $txt .= "\n";
        $txt .= "\n";

/*

        //$txt = "Test \n";
        foreach ($this->placecode_list as $place_code) {
    
            $txt .= " " . str_pad(" ", 40, " ", STR_PAD_RIGHT);

            $txt .= " " . "  " . str_pad(strtoupper($place_code), 5, "X", STR_PAD_LEFT);
            //$txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);

            $txt .= "\n";



        }


        foreach ($this->placename_list as $place_name) {

            $txt .= " " . str_pad(strtoupper($place_name), 40, " ", STR_PAD_RIGHT);

            $txt .= "\n";



        }
*/
        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->resources as $key=>$resource) {

            $txt .= " " . str_pad(strtoupper($resource['name']), 40, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper($resource['quantity']), 5, "X", STR_PAD_LEFT);
            

            $txt .= "\n";



        }




        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    public function makeSMS() {

$s = "NOT USED";
        $sms_message = "RESOURCE " . strtoupper($this->resource_name) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | ";

if (!isset($this->resource_quantity)) {$this->resource_quantity = "X";}

        $sms_message .= strtoupper($this->resource_quantity);
 
//        $sms_message .= " | index " . $this->index;

//        $sms_message .= " | nuuid " . strtoupper($this->place->nuuid);
//        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

//        $sms_message .= " | ptime " . number_format($this->place->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "resource";


		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        //$this->makeTXT();


// Get available for place.  This would be an available agent.
//$available = $this->thing->human_time($this->available);

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;//
        }

        $this->makeSMS();



  

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>headcode state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

//        $test_message .= '<br>run_at: ' . $this->run_at;
//        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			//$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $this->sms_message;
			$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is a Resource.  A quantity of Things.';

		return;
	}

    function isData($variable) {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
            return true;

        } else {
            return false;
        }
    }

    public function readSubject() 
    {

        $this->response = null;
        $this->num_hits = 0;


       // $keywords = $this->keywords;
/*
        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.

            // 14 Mar 2018.    devstack to Agent.
            $input = strtolower($this->agent_input);
        } elseif ($this-agent_input == "extract") {
            $input = strtolower($this->from . " " . $this->subject);
        } else {
            $input = strtolower($this->from . " " . $this->subject);
        }
*/



switch (true) {
    case ($this->agent_input == "extract"):
        $input = strtolower($this->from . " " . $this->subject);
        break;
    case ($this->agent_input != null):
        $input = strtolower($this->agent_input);
        break;
    case (true):
        $input = strtolower($this->subject);
}

        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractResource($input);
        $this->getNumber($input);
        if ($this->agent_input == "extract") {return;}



//        if (($this->place_name == null) or ($this->place_code == null)) {
//        }

        // Return the current place

        $this->last_resource = new Variables($this->thing, "variables resource " . $this->from);
        $this->last_resource_quantity = $this->last_resource->getVariable('resource_quantity');
        $this->last_resource_name = $this->last_resource->getVariable('resource_name');

        // If at this point we get false/false, then the default Place has not been created.

        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
/*
        if (count($pieces) == 1) {

            if ($input == 'place') {
                $this->read();
                return;
            }

        }
*/
    foreach ($pieces as $key=>$piece) {
        foreach ($this->keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {

    case 'next':
        $this->thing->log("read subject nextheadcode");
        $this->nextResource();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropResource();
        break;

   case 'make':
   case 'new':
   case 'create':
   case 'place':
   case 'add':
//exit();

    
      //$this->nextCode();
        $this->assertResource(strtolower($input));
//        if ($this->place_name == null) {$this->place_name = "Foo" . rand(0,1000000) . "Bar";}

        //$this->makeheadcode();
        //$this->makePlace($this->place_code, $this->place_name);
        //$this->getPlace($this->place_code);
        //$this->set();
        return;
        break;


    default:

                                        }

                                }
                        }

                }


        // If at this point we get false/false, then the default Place has not been created.
//        if ( ($this->place_code == false) and ($this->place_name == false) ) {
//            $this->makePlace($this->default_place_code, $this->default_place_name);
//        }

//var_dump($this->last_place_code);
//var_dump($this->last_place_name);
//echo "<bR>";
//var_dump($this->place_code);
//var_dump($this->place_name);


    if ($this->resource_name != null) {
        $this->getResource($this->resource_name);
        $this->thing->log($this->agent_prefix . 'using extracted resource_name ' . $this->resource_name . ".","INFORMATION");
        return;
    }

    if ($this->last_resource_name != null) {
        $this->getResource($this->last_resource_name);
        $this->thing->log($this->agent_prefix . 'using extracted last_resource_name ' . $this->last_resource_name . ".","INFORMATION");
        return;
    }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name
//var_dump($this->subject);
//echo $this->subject;
//$place = "dogpark";

$resource = strtolower($this->subject);

if ( !$this->getResource(strtolower($resource)) ){
    // Place was found
    // And loaded
    return;
}


//    function makePlace($place_code = null, $place_name = null) {
$this->makeResource($resource);
$this->set();

$this->thing->log($this->agent_prefix . 'using default_resource_name ' . $this->default_resource_name . ".","INFORMATION");

//$this->getPlace($this->default_place_code);
//$this->getPlace($place);


            return;


    if (($this->isData($this->resource_name)) or ($this->isData($this->resource_quantity)) ) {
        $this->set();
        return;
    }

    $this->read();




                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

}

/* More on places

Lots of different ways to number places.



*/
?>

