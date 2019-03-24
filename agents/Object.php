<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Object extends Agent
{
    // Timing 11 July 2018 
    // 4,232ms, 4,009ms, 3,057ms

    // This is a place.

    //

    // This is an agent of a place.  They can probaby do a lot for somebody.
    // With the right questions.

    public $var = 'hello';

    function init() 
    //function __construct(Thing $thing, $agent_input = null) 
    {

//         $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}
//        $this->agent_input = $agent_input;

//        $this->thing = $thing;
//        $this->start_time = $this->thing->elapsed_runtime();
//        $this->thing_report['thing'] = $this->thing->thing;
//        $this->agent_name = "object";
//        $this->agent_prefix = 'Agent "Object" ';

//        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");

        // I'm not sure quite what the node_list means yet
        // in the context of headcodes.
        // At the moment it seems to be the headcode routing.
        // Which is leading to me to question whether "is"
        // or "Place" is the next Agent to code up.  I think
        // it will be "Is" because you have to define what 
        // a "Place [is]".
 //       $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
 //       $this->thing->choice->load('headcode');

        // Get some stuff from the stack which will be helpful.
//        $this->web_prefix = $thing->container['stack']['web_prefix'];
//        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
//        $this->word = $thing->container['stack']['word'];
//        $this->email = $thing->container['stack']['email'];

        $this->keywords = array('object','next', 'accept', 'clear', 'drop','add','new','here','there');

        $this->default_object_name = $this->thing->container['api']['object']['default_object_name'];
        $this->default_object_code = $this->thing->container['api']['object']['default_object_code'];

//        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->default_alias = "Thing";
//        $this->current_time = $this->thing->time();

		$this->test= "Development code"; // Always iterative.

        // Non-nominal
//        $this->uuid = $thing->uuid;
//        $this->to = $thing->to;

        // Potentially nominal
//        $this->subject = $thing->subject;

        // Treat as nominal
//        $this->from = $thing->from;

        // Agent variables
//        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->state = null; // to avoid error messages

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/object';


        $this->lastObject();

        // Read the subject to determine intent.

        $this->railway_object = new Variables($this->thing, "variables object " . $this->from);


		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();

//        if ($this->agent_input == null) {
//		    $this->Respond();
//        }

        if ($this->agent_input != "extract") {
            $this->set();
        }

		return;
    }


    public function set()
    {
        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}
        //$this->refreshed_at = $this->current_time;
        $object = new Variables($this->thing, "variables object " . $this->from);

        $object->setVariable("object_code", $this->object_code);
        $object->setVariable("object_name", $this->object_name);

        $object->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log( $this->agent_prefix .' set ' . $this->object_code . ' and ' . $this->object_name . ".", "INFORMATION" );

        $object = new Variables($this->thing, "variables " . $this->object_code . " " . $this->from);
        $object->setVariable("object_name", $this->object_name);
        $object->setVariable("refreshed_at", $this->refreshed_at);
    }

    function isCode()
    {
        $object_zone = "05";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);


        foreach (range(1,9999) as $n) {
            foreach($this->objects as $object) {

                $object_code = $object_zone . str_pad($n, 4, "0", STR_PAD_LEFT);

                if ($this->getObject($object_code)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log("No Object code available in zone " . $object_zone .".", "WARNING");
                return;
            }
        }
}

    function nextCode()
    {
        //$object_code_candidate = null;
        $object_code_candidate = $this->thing->nuuid;

        foreach ($this->objects as $object) {
            $object_code = strtolower($object['code']);
            if (($object_code == $object_code_candidate) or ($object_code_candidate == null)) {
                $thing = new Thing(null);
                $object_code_candidate = $thing->nuuid;
//                $object_code_candidate = str_pad(rand(100,9999) , 8, "9", STR_PAD_LEFT);
            }
        }

//        $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
        return $object_code;

    }

    function nextObject()
    {
        $this->thing->log("next object");
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
/*
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
*/
    function getObject($selector = null)
    {
        foreach ($this->objects as $object) {
            // Match the first matching object
            if (($selector == null) or ($selector == "")) {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->object_name = $this->last_object_name;
                $this->object_code = $this->last_object_code;
                $this->object = new Variables($this->thing, "variables " . $this->object_code . " " . $this->from);
                return array($this->object_code, $this->object_name);
            }

            if (($object['code'] == $selector) or ($object['name'] == $selector)) {
                $this->refreshed_at = $object['refreshed_at'];
                $this->object_name = $object['name'];
                $this->object_code = $object['code'];
                $this->object = new Variables($this->thing, "variables " . $this->object_code . " " . $this->from);
                return array($this->object_code, $this->object_name);
            }
       }

       return true;
    }

    function getObjects()
    {
        $this->objectcode_list = array();
        $this->objectname_list = array();
        $this->objects = array();

        // See if a headcode record exists.
        $findagent_thing = new FindAgent($this->thing, 'object');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Object" found ' . count($findagent_thing->thing_report['things']) ." object Things." );

//        if ($findagent_thing->thing_reports['things'] == false) {
//                $place_code = $this->default_place_code;
//                $place_name = $this->default_place_name;
//            return array($this->placecode_list, $this->placename_list, $this->places);
//        }

        if ( ($findagent_thing->thing_report['things'] == true)) {}

        //var_dump(count($findagent_thing->thing_report['things'])); 
        //var_dump($findagent_thing->thing_report['things'] == true);


        if (!$this->is_positive_integer($count))
        {
            // No places found
        } else {




            foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object)
            {
                $uuid = $thing_object['uuid'];

                $variables_json= $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['object'])) {

                    $object_code = $this->default_object_code;
                    $object_name = $this->default_object_name;
                    $refreshed_at = "meep getObjects";

                    if(isset($variables['object']['object_code'])) {$object_code = $variables['object']['object_code'];}
                    if(isset($variables['object']['object_name'])) {$object_name = $variables['object']['object_name'];}
                    if(isset($variables['object']['refreshed_at'])) {$refreshed_at = $variables['object']['refreshed_at'];}

/*
                    // Check if the place is already in the the list (this->places)
                    $found = false;
                    foreach(array_reverse($this->places) as $key=>$place) {

                        if ($place["name"] == $place_name) {
                            // Found place in list.  Don't add again.
                            $found = true;
                            break;
                        }
                    }

$found = false;

*/
//                    if ($found == false) {




                        $this->objects[] = array("code"=>$object_code, "name"=>$object_name, "refreshed_at"=>$refreshed_at);
                        $this->objectcode_list[] = $object_code;
                        $this->objectname_list[] = $object_name;
  //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_objects = array();
        foreach(array_reverse($this->objects) as $key=>$object) {

            $object_name = $object['name'];
            $object_code = $object['code'];

            if (!isset($object['refreshed_at'])) {continue;}

            $refreshed_at = $object['refreshed_at'];

            if (isset($filtered_objects[$object_name]['refreshed_at'])) {
                if (strtotime($refreshed_at) > strtotime($filtered_objects[$object_name]['refreshed_at'])) {
                    $filtered_objectss[$object_name] = array("name"=>$object_name, "code"=>$object_code, 'refreshed_at' => $refreshed_at);
                }
                continue;
            } 

            $filtered_objects[$object_name] = array("name"=>$object_name, "code"=>$object_code, 'refreshed_at' => $refreshed_at);


        }

        $refreshed_at = array();
        foreach ($this->objects as $key => $row)
        {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->objects);

/*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
$this->old_objects = $this->objects;
$this->objects = array();
foreach ($this->old_objects as $key =>$row)
{

//var_dump( strtotime($row['refreshed_at']) );
    if ( strtotime($row['refreshed_at']) != false) { 
      $this->objects[] = $row;
    }
}

//exit();
    //exit();

        // Add in a set of default objects
         $file = $this->resource_path .'object/objects.txt';
         $contents = file_get_contents($file);

        $handle = fopen($file, "r");


        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of object names.
                // Common ones.
                $object_name = $line;
                // This is where the object index will be called.
                //$object_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
                $thing = new Thing(null);
                $object_code = $thing->nuuid;


                $this->objectcode_list[] = $object_code;
                $this->objectname_list[] = $object_name;
                $this->objects[] = array("code"=>$object_code, "name"=>$object_name); 

            }

            fclose($handle);
        } else {
            // error opening the file.
        }

       // Indexing not implemented
        $this->max_index = 0;

        return array($this->objectcode_list, $this->objectname_list, $this->objects);

    }

    function is_positive_integer($str)
    {
        return (is_numeric($str) && $str > 0 && $str == round($str));
    }

    public function get($object_code = null)
    {
        // This is a request to get the Object from the Thing
        // and if that doesn't work then from the Stack.
        if ($object_code == null) {$object_code = $this->object_code;}

        $this->object = new Variables($this->thing, "variables " . $object_code . " " . $this->from);

        $this->object_code = $this->object->getVariable("object_code");
        $this->object_name = $this->object->getVariable("object_name");
        $this->refreshed_at = $this->object->getVariable("refreshed_at");

        return array($this->object_code, $this->object_name);
    }

    function dropObject() {
        $this->thing->log($this->agent_prefix . "was asked to drop an Object.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->object)) {
            $this->object->Forget();
            $this->object = null;
        }

        $this->get();
 
    }


    function makeObject($object_code = null, $object_name = null)
    {
        if ($object_name == null) {return true;}

        // See if the code or name already exists
        foreach ($this->objects as $object) {
            if (($object_code == $object['code']) or ($object_name == $object['name'])) {
                $this->object_name = $object['name'];
                $object_code =$object['code'];
                $this->last_refreshed_at = $object['refreshed_at'];
            }
        }
        if ($object_code == null) {$object_code = $this->nextCode();}

        $this->thing->log('will make a Object for ' . $object_code . ".");

        $ad_hoc = true;
        $this->thing->log($this->agent_prefix . "is ready to make a Object.");
        if ( ($ad_hoc != false) ) {
            $this->thing->log("is making a Object.");
            $this->thing->log("was told the Object is Useable but we might get kicked out.");

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($object_code == false) {
                $object_code = $this->default_object_code;
                $object_name = $this->default_object_name;
            }

            $this->current_object_code = $object_code;
            $this->object_code = $object_code;

            $this->current_object_name = $object_name;
            $this->object_name = $object_name;
            $this->refreshed_at = $this->current_time;
  
            // This will write the refreshed at.
            $this->set();

            $this->getObjects();
            $this->getObject($this->object_code);

            $this->object_thing = $this->thing;

        }

        $this->thing->log('Agent "Object" found a Object and pointed to it.');
    }

    function objectTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $object_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $object_time = $this->hour . $this->minute;

        if ($input == null) {$this->object_time = $object_time;}

        return $object_time;
    }

    public function extractObjects($input = null)
    {

        if (!isset($this->object_codes)) {
            $this->object_codes = array();
        }

        if (!isset($this->object_names)) {
            $this->object_names = array();
        }

        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{6}$|";

        preg_match_all($pattern, $input, $m);
        $this->object_codes = $m[0];


        // Look for an established list of objects.
        //$default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        //if (!isset($this->place_name_list)) {$this->get();}

        //$this->place_names = array();
        //foreach ($places as $place) {

        if (!isset($this->objects)) {$this->getObjects();}

        foreach ($this->objects as $object) {
            $object_name = strtolower($object['name']);
            $object_code = strtolower($object['code']);

            if (empty($object_name)) {continue;}
            if (empty($object_code)) {continue;}
//if ($place_name == null) {continue;}
//if ($place_code == null) {continue;}
//exit();

            // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
            if (strpos($input, $object_code) !== false)  {
                $this->object_codes[] = $object_code;
            }

            if (strpos($input, $object_name) !== false)  {
                $this->object_names[] = $object_name;
            }

        }

        $this->object_codes = array_unique($this->object_codes);
        $this->object_names = array_unique($this->object_names);

        return array($this->object_codes, $this->object_names);
    }

    public function extractObject($input)
    {
        $this->object_name = null;
        $this->object_code = null;

        list($object_codes,$object_names) = $this->extractObjects($input);

        if ( ( count($object_codes) + count($object_names) ) == 1) {
            if (isset($object_codes[0])) {$this->object_code = $object_codes[0];}
            if (isset($object_names[0])) {$this->object_name = $object_names[0];}

            $this->thing->log( $this->agent_prefix  . 'found a object code (' . $this->object_code . ') in the text.');
            return array($this->object_code, $this->object_name);
        }


        //if (count($place_codes == 0)) {return false;}
        //if (count($place_codes > 1)) {return true;}

        // And then extract place names.
        // Take out word 'place' at the start.
//        $filtered_input = ltrim(strtolower($input), "place");


//exit();


        if (count($object_names) == 1) {$this->object_name = $this->object_names[0];}
//var_dump($place_names);
        return array($this->object_code, $this->object_name);
    }

    function assertObject($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "object is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("object is")); 
        } elseif (($pos = strpos(strtolower($input), "object")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("object")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $object = $this->getObject($filtered_input);
        if ($object) {
            //true so make a object
            $this->makeObject(null, $filtered_input);
        }
    }


    public function read()
    {
        $this->thing->log("read.");

//        $this->get();
        //return $this->available;
    }



    function addObject() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    public function makeMessage()
    {
        $message = "Object is ". ucwords($this->object_name) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    function makeTXT()
    {
        if (!isset($this->objectcode_list)) {$this->getObjects();}

        $this->getObjects();

        if (!isset($this->object)) {$txt = "Not here";} else {

        $txt = 'These are OBJECTS for OBJECT ' . $this->railway_object->nuuid . '. ';
        }
        $txt .= "\n";
//        $txt .= count($this->placecode_list). ' Place codes and names retrieved.';

        $txt .= "\n";


        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CODE", 8, " ", STR_PAD_LEFT);


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
        foreach ($this->objects as $key=>$object) {

            $txt .= " " . str_pad(strtoupper(trim($object['name'])), 40, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper(trim($object['code'])), 5, "X", STR_PAD_LEFT);
            if (isset($object['refreshed_at'])) {
                $txt .= " " . "  " .str_pad(strtoupper($object['refreshed_at']), 15, "X", STR_PAD_LEFT);
            }
            $txt .= "\n";



        }

        $txt .= "\n";
        $txt .= "Last object " . $this->last_object_name . "\n";
        $txt .= "Now at " . $this->object_name;



        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    private function makeSMS() {

        $this->inject = null;
        $s = $this->inject;
        $sms = "OBJECT " . strtoupper($this->object_name);

        if ((!empty($this->inject))) {
            $sms .= " | " . $s;
        } 

        if ((!empty($this->object_code))) {
        $sms .= " | " . trim(strtoupper($this->object_code));
        }

        if ((!empty($this->object_code))) {
            $sms .= " | " . $this->web_prefix . 'thing/' . $this->uuid . '/object';
        }


/*

//        if (!isset($this->last_refreshed_at) {$this->lastPlace();}

        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->last_refreshed_at) );
        $sms .= " | (Last) Place set about ". $ago . " ago.";

        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->refreshed_at) );
        $sms .= " | Place set about ". $ago . " ago.";

        $time = $this->last_refreshed_at;
        $sms .= " | (Last) Place set " . $this->refreshed_at . " " . $this->last_refreshed_at . ".";

        $time = $this->last_refreshed_at;
        $sms .= " | Place set " . $this->refreshed_at . " " . $this->refreshed_at . ".";


        if (isset($this->response)) {
            $sms .= " | " . $this->response;
        }
        //$sms .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

*/

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;


    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/object.txt';
        

        $this->node_list = array("object"=>array("translink", "job"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "object");
        $choices = $this->thing->choice->makeLinks('object');

        $web = '<a href="' . $link . '">';

// Insert other agents images...
// $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg" 
//      width="100" height="100" 
//      alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';
// or
// $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';


// Get aan html image if there is one
if (!isset($this->html_image)) {
    if (function_exists("makePNG")) {
        $this->makePNG();
    } else {
        $this->html_image = true;
    }
}

$this->makePNG();
        $web .= $this->html_image;
$web .= "<br>";

        $web .= "</a>";
        $web .= "<br>";
$web .= $this->sms_message;
        $web .= "<br>";


        
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/object.txt';
        $web .= '<a href="' . $link . '">object.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/object.log';
        $web .= '<a href="' . $link . '">object.log</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/'. "object";
        $web .= $this->object_name. '';
        $web .= " | ";
        $web .= '<a href="' . $link . '">'. "object" . '</a>';



        $web .= "<br>";



        $web .= "<br>";



        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->refreshed_at) );
        $web .= "Last asserted about ". $ago . " ago.";

        $web .= "<br>";


        $this->thing_report['web'] = $web;
    }


    public function makeImage()
    {
        $text = strtoupper($this->object_name);

$image_height = 125;
$image_width = 125*4;

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        $this->ImageRectangleWithRoundedCorners($image, 0,0, $image_width, $image_height, 12, $black);
        $this->ImageRectangleWithRoundedCorners($image, 6,6, $image_width-6, $image_height-6, 12-6, $white);

        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = array(72,36,24,12,6);

        foreach($sizes_allowed as $size) {

            $angle = 0;
            $bbox = imagettfbbox ($size, $angle, $font, $text); 
            $bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
            $bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
            $bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
            $bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
            extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 

            //check width of the image 
            $width = imagesx($image); 
            $height = imagesy($image);
            if ($bbox['width'] < $image_width - 50) {break;}

        }

        $pad = 0;
        imagettftext($image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $text);
        imagestring($image, 2, $image_width-75, 10, $this->object_code, $textcolor);

        $this->image = $image;
    }

    public function makePNG()
    {
        $agent = new Png($this->thing, "png"); // long run

        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report['png'] = $agent->image_string;
    }

/*
    public function old_makePNG()
    {
$split_time = $this->thing->elapsed_runtime();

        // Save the image
        $this->makeImage();
        $image = $this->image;
        ob_start();
        imagepng($image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $alt_text = "Place is " . $this->place_name;


        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata). '"
                width="100" height="100" 
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';

        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata). '"
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';


        $this->html_image = $response;

//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg" 
//                width="100" height="100" 
//                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';


//        $this->thing_report['png'] = $image;

        imagedestroy($image);

echo $this->thing->elapsed_runtime() - $split_time; 


        return $response;

//        $this->PNG = $image;    
//        $this->thing_report['png'] = $image;

//       return;
    }
*/
    function ImageRectangleWithRoundedCorners(&$im, $x1, $y1, $x2, $y2, $radius, $color)
    {
        // draw rectangle without corners
        imagefilledrectangle($im, $x1+$radius, $y1, $x2-$radius, $y2, $color);
        imagefilledrectangle($im, $x1, $y1+$radius, $x2, $y2-$radius, $color);

        // draw circled corners
        imagefilledellipse($im, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
    }


	public function respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "object";


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

/*
		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>headcode state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;
*/
    	$this->thing_report['email'] = $this->sms_message;
		//$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->makeMessage();

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeWeb();
        $this->makeTXT();

        $this->thing_report['help'] = 'This is an Object.  The union of a code and a name.';

		return;
	}

    function isData($variable)
    {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
            return true;

        } else {
            return false;
        }
    }

    function lastObject() {

        $this->last_object = new Variables($this->thing, "variables object " . $this->from);
        $this->last_object_code = $this->last_object->getVariable('object_code');
        $this->last_object_name = $this->last_object->getVariable('object_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_object->getVariable('refreshed_at');
return;
        // So do it the hard way

        if (!isset($this->objects)) {$this->getObjects();}

        foreach(array_reverse($this->objects) as $key=>$object) {

            if ($object['name'] == $this->last_object_name) {
                $this->last_refreshed_at = $object['refreshed_at'];
                break;
            }


        }

        //echo "last object refreshed at " .$this->last_refreshed_at;
        //echo "<br>";


    }

    public function readSubject()
    {

        $this->response = null;
        $this->num_hits = 0;

        switch (true) {
            case ($this->agent_input == "extract"):
                $input = strtolower($this->from . " " . $this->subject);
                break;
            case (isset($this->agent_input)):
                $input = strtolower($this->agent_input);
                break;
            case ($this->agent_input == null):
                $input = strtolower($this->from . " " . $this->subject);
                break;
/*
            case (true):
                // What is going to be found in from?
                // Allows place name extraction from email address.
                // Allows place code extraction from "from"
                // Internet of Things.  Sometimes all you have is the Thing's address.
                // And that's a Place.
                $input = strtolower($this->from . " " . $this->subject);
 */
       }

//var_dump($input);
/*
        if (isset($this->agent_input)) {
            $input = $this->agent_input;
        } else {
            $input = strtolower($this->subject);
        }
        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;
        //$haystack = $this->agent_input . " " . $this->from;
        $haystack = $input . " " . $this->from;
*/

        // Would normally just use a haystack.
        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
		// $haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a object in the provided datagram
        $this->extractObject($input);
        if ($this->agent_input == "extract") {return;}

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'object') {
                $this->getObject();
                $this->response = "Last 'object' retrieved.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'next':
                            $this->thing->log("read subject nextheadcode");
        $this->nextObject();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropObject();
        break;

   case 'make':
   case 'new':
   case 'object':
   case 'create':
   case 'add':
        $this->assertObject(strtolower($input));

        //$this->refreshed_at = $this->thing->time();

        if (empty($this->object_name)) {$this->object_name = "X";}

        $this->response = 'Asserted Object and found ' . strtoupper($this->object_name) .".";
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

    if ($this->object_code != null) {
        $this->getObject($this->object_code);
        $this->thing->log($this->agent_prefix . 'using extracted object_code ' . $this->object_code . ".","INFORMATION");
        $this->response = $this->object_code . " used to retrieve a Object.";

        return;
    }

    if ($this->object_name != null) {

        $this->getObject($this->object_name);

        $this->thing->log($this->agent_prefix . 'using extracted object_name ' . $this->object_name . ".","INFORMATION");
        $this->response = strtoupper($this->object_name) . " retrieved.";
$this->assertObject($this->object_name);
        return;
    }

    if ($this->last_object_code != null) {
        $this->getObject($this->last_object_code);
        $this->thing->log($this->agent_prefix . 'using extracted last_object_code ' . $this->last_object_code . ".","INFORMATION");
        $this->response = "Last object " . $this->last_object_code . " used to retrieve a Object.";

        return;
    }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

$place = strtolower($this->subject);

if ( !$this->getObject(strtolower($object)) ){
    // Place was found
    // And loaded
    $this->response = $object . " used to retrieve a Object.";

    return;
}


        $this->makeObject(null, $object);
        $this->thing->log($this->agent_prefix . 'using default_object_code ' . $this->default_object_code . ".","INFORMATION");

        $this->response = "Made a Object called " . $object . ".";
        return;

        if (($this->isData($this->object_name)) or ($this->isData($this->object_code)) ) {
            $this->set();
            return;
        }

		return false;

	}
/*
	public function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}
*/

}

/* More on places

Lots of different ways to number places.

*/
?>
