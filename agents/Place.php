<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Place
{
    // Timing 11 July 2018 
    // 4,232ms, 4,009ms, 3,057ms

    // This is a place.

    //

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
        $this->agent_name = "place";
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

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];



        $this->keywords = array('place','next', 'accept', 'clear', 'drop','add','new','here','there');

        $this->default_place_name = $this->thing->container['api']['place']['default_place_name'];
        $this->default_place_code = $this->thing->container['api']['place']['default_place_code'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->default_alias = "Thing";
        $this->current_time = $this->thing->time();

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

        $this->lastPlace();

        // Read the subject to determine intent.

        $this->railway_place = new Variables($this->thing, "variables place " . $this->from);


		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();

        if ($this->agent_input == null) {
		    $this->Respond();
        }

        if ($this->agent_input != "extract") {
            $this->set();
        }

        $this->thing->log( $this->agent_prefix .' loaded place_name ' . $this->place_name . " and place_code " . $this->place_code . "." );

//		$this->thing->log('<pre> Agent "Headcode" completed</pre>');
        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;


		return;
    }


    function set()
    {

        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}
        //$this->refreshed_at = $this->current_time;
        $place = new Variables($this->thing, "variables place " . $this->from);

        $place->setVariable("place_code", $this->place_code);
        $place->setVariable("place_name", $this->place_name);

        $place->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log( $this->agent_prefix .' set ' . $this->place_code . ' and ' . $this->place_name . ".", "INFORMATION" );

        $place = new Variables($this->thing, "variables " . $this->place_code . " " . $this->from);
        $place->setVariable("place_name", $this->place_name);
        $place->setVariable("refreshed_at", $this->refreshed_at);


        return;
    }

    function isCode()
    {
        $place_zone = "05";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);


        foreach (range(1,9999) as $n) {
            foreach($this->places as $place) {

                $place_code = $place_zone . str_pad($n, 4, "0", STR_PAD_LEFT);

                if ($this->getPlace($place_code)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log("No Place code available in zone " . $place_zone .".", "WARNING");
                return;
            }
        }
}

    function nextCode() {


        $place_code_candidate = null;

        foreach ($this->places as $place) {
            $place_code = strtolower($place['code']);
            if (($place_code == $place_code_candidate) or ($place_code_candidate == null)) {
                $place_code_candidate = str_pad(rand(100,9999) , 8, "9", STR_PAD_LEFT);
            }
        }

//        $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
        return $place_code;

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

    function getPlace($selector = null)
    {
        foreach ($this->places as $place) {
            // Match the first matching place
            if (($selector == null) or ($selector == "")) {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->place_name = $this->last_place_name;
                $this->place_code = $this->last_place_code;
                $this->place = new Variables($this->thing, "variables " . $this->place_code . " " . $this->from);
                return array($this->place_code, $this->place_name);
            }

            if (($place['code'] == $selector) or ($place['name'] == $selector)) {
                $this->refreshed_at = $place['refreshed_at'];
                $this->place_name = $place['name'];
                $this->place_code = $place['code'];
                $this->place = new Variables($this->thing, "variables " . $this->place_code . " " . $this->from);
                return array($this->place_code, $this->place_name);
            }
       }

       return true;
    }

    function getPlaces()
    {
        $this->placecode_list = array();
        $this->placename_list = array();
        $this->places = array();

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'place');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Place" found ' . count($findagent_thing->thing_report['things']) ." place Things." );

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

                if (isset($variables['place'])) {

                    $place_code = $this->default_place_code;
                    $place_name = $this->default_place_name;
                    $refreshed_at = "meep getPlaces";

                    if(isset($variables['place']['place_code'])) {$place_code = $variables['place']['place_code'];}
                    if(isset($variables['place']['place_name'])) {$place_name = $variables['place']['place_name'];}
                    if(isset($variables['place']['refreshed_at'])) {$refreshed_at = $variables['place']['refreshed_at'];}

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




                        $this->places[] = array("code"=>$place_code, "name"=>$place_name, "refreshed_at"=>$refreshed_at);
                        $this->placecode_list[] = $place_code;
                        $this->placename_list[] = $place_name;
  //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_places = array();
        foreach(array_reverse($this->places) as $key=>$place) {

            $place_name = $place['name'];
            $place_code = $place['code'];

            if (!isset($place['refreshed_at'])) {continue;}

            $refreshed_at = $place['refreshed_at'];

            if (isset($filtered_places[$place_name]['refreshed_at'])) {
                if (strtotime($refreshed_at) > strtotime($filtered_places[$place_name]['refreshed_at'])) {
                    $filtered_places[$place_name] = array("name"=>$place_name, "code"=>$place_code, 'refreshed_at' => $refreshed_at);
                }
                continue;
            } 

            $filtered_places[$place_name] = array("name"=>$place_name, "code"=>$place_code, 'refreshed_at' => $refreshed_at);


        }

$refreshed_at = array();
foreach ($this->places as $key => $row)
{
    $refreshed_at[$key] = $row['refreshed_at'];
}
array_multisort($refreshed_at, SORT_DESC, $this->places);

/*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
$this->old_places = $this->places;
$this->places = array();
foreach ($this->old_places as $key =>$row)
{

//var_dump( strtotime($row['refreshed_at']) );
    if ( strtotime($row['refreshed_at']) != false) { 
      $this->places[] = $row;
    }
}

//exit();
    //exit();

        // Add in a set of default places
         $file = $this->resource_path .'place/places.txt';
         $contents = file_get_contents($file);

        $handle = fopen($file, "r");


        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $place_name = $line;
                // This is where the place index will be called.
                $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

                $this->placecode_list[] = $place_code;
                $this->placename_list[] = $place_name;
                $this->places[] = array("code"=>$place_code, "name"=>$place_name); 

            }

            fclose($handle);
        } else {
            // error opening the file.
        }

       // Indexing not implemented
        $this->max_index = 0;

        return array($this->placecode_list, $this->placename_list, $this->places);

    }

    function is_positive_integer($str)
    {
        return (is_numeric($str) && $str > 0 && $str == round($str));
    }

    private function get($place_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($place_code == null) {$place_code = $this->place_code;}

        $this->place = new Variables($this->thing, "variables " . $place_code . " " . $this->from);

        $this->place_code = $this->place->getVariable("place_code");
        $this->place_name = $this->place->getVariable("place_name");
        $this->refreshed_at = $this->place->getVariable("refreshed_at");

        return array($this->place_code, $this->place_name);
    }

    function dropPlace() {
        $this->thing->log($this->agent_prefix . "was asked to drop a Place.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->place)) {
            $this->place->Forget();
            $this->place = null;
        }

        $this->get();
 
    }


    function makePlace($place_code = null, $place_name = null)
    {
        if ($place_name == null) {return true;}

        // See if the code or name already exists
        foreach ($this->places as $place) {
            if (($place_code == $place['code']) or ($place_name == $place['name'])) {
                $this->place_name = $place['name'];
                $place_code =$place['code'];
                $this->last_refreshed_at = $place['refreshed_at'];
            }
        }
        if ($place_code == null) {$place_code = $this->nextCode();}

        $this->thing->log('Agent "Place" will make a Place for ' . $place_code . ".");

        $ad_hoc = true;
        $this->thing->log($this->agent_prefix . "is ready to make a Place.");
        if ( ($ad_hoc != false) ) {
            $this->thing->log($this->agent_prefix . "is making a Place.");
            $this->thing->log($this->agent_prefix . "was told the Place is Useable but we might get kicked out.");

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($place_code == false) {
                $place_code = $this->default_place_code;
                $place_name = $this->default_place_name;
            }

            $this->current_place_code = $place_code;
            $this->place_code = $place_code;

            $this->current_place_name = $place_name;
            $this->place_name = $place_name;
            $this->refreshed_at = $this->current_time;
  
            // This will write the refreshed at.
            $this->set();

            $this->getPlaces();
            $this->getPlace($this->place_code);

            $this->place_thing = $this->thing;

        }

        $this->thing->log('Agent "Place" found a Place and pointed to it.');
    }

    function placeTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $place_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $place_time = $this->hour . $this->minute;

        if ($input == null) {$this->place_time = $place_time;}

        return $place_time;
    }

    public function extractPlaces($input = null)
    {

        if (!isset($this->place_codes)) {
            $this->place_codes = array();
        }

        if (!isset($this->place_names)) {
            $this->place_names = array();
        }

        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{6}$|";

        preg_match_all($pattern, $input, $m);
        $this->place_codes = $m[0];


        // Look for an established list of places.
        //$default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        //if (!isset($this->place_name_list)) {$this->get();}

        //$this->place_names = array();
        //foreach ($places as $place) {

        if (!isset($this->places)) {$this->getPlaces();}

        foreach ($this->places as $place) {
            $place_name = strtolower($place['name']);
            $place_code = strtolower($place['code']);

            if (empty($place_name)) {continue;}
            if (empty($place_code)) {continue;}
//if ($place_name == null) {continue;}
//if ($place_code == null) {continue;}
//exit();

            // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
            if (strpos($input, $place_code) !== false)  {
                $this->place_codes[] = $place_code;
            }

            if (strpos($input, $place_name) !== false)  {
                $this->place_names[] = $place_name;
            }

        }

        $this->place_codes = array_unique($this->place_codes);
        $this->place_names = array_unique($this->place_names);

        return array($this->place_codes, $this->place_names);
    }

    public function extractPlace($input)
    {
        $this->place_name = null;
        $this->place_code = null;

        list($place_codes,$place_names) = $this->extractPlaces($input);

        if ( ( count($place_codes) + count($place_names) ) == 1) {
            if (isset($place_codes[0])) {$this->place_code = $place_codes[0];}
            if (isset($place_names[0])) {$this->place_name = $place_names[0];}

            $this->thing->log( $this->agent_prefix  . 'found a place code (' . $this->place_code . ') in the text.');
            return array($this->place_code, $this->place_name);
        }


        //if (count($place_codes == 0)) {return false;}
        //if (count($place_codes > 1)) {return true;}

        // And then extract place names.
        // Take out word 'place' at the start.
//        $filtered_input = ltrim(strtolower($input), "place");


//exit();


        if (count($place_names) == 1) {$this->place_name = $this->place_names[0];}
//var_dump($place_names);
        return array($this->place_code, $this->place_name);
    }

    function assertPlace($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "place is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("place is")); 
        } elseif (($pos = strpos(strtolower($input), "place")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("place")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $place = $this->getPlace($filtered_input);
        if ($place) {
            //true so make a place
            $this->makePlace(null, $filtered_input);
        }
    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        //return $this->available;
    }



    function addPlace() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    public function makeMessage()
    {
        $message = "Place is ". ucwords($this->place_name) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    function makeTXT()
    {
        if (!isset($this->placecode_list)) {$this->getPlaces();}

        $this->getPlaces();

        if (!isset($this->place)) {$txt = "Not here";} else {

        $txt = 'These are PLACES for PLACE ' . $this->railway_place->nuuid . '. ';
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
        foreach ($this->places as $key=>$place) {

            $txt .= " " . str_pad(strtoupper(trim($place['name'])), 40, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper(trim($place['code'])), 5, "X", STR_PAD_LEFT);
            if (isset($place['refreshed_at'])) {
                $txt .= " " . "  " .str_pad(strtoupper($place['refreshed_at']), 15, "X", STR_PAD_LEFT);
            }
            $txt .= "\n";



        }

        $txt .= "\n";
        $txt .= "Last place " . $this->last_place_name . "\n";
        $txt .= "Now at " . $this->place_name;



        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    private function makeSMS() {

        $this->inject = null;
        $s = $this->inject;
        $sms = "PLACE " . strtoupper($this->place_name);

        if ((!empty($this->inject))) {
            $sms .= " | " . $s;
        } 

        if ((!empty($this->place_code))) {
        $sms .= " | " . trim(strtoupper($this->place_code));
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

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/place.txt';
        

        $this->node_list = array("place"=>array("translink", "job"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "place");
        $choices = $this->thing->choice->makeLinks('place');

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


        
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.txt';
        $web .= '<a href="' . $link . '">place.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.log';
        $web .= '<a href="' . $link . '">place.log</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/'. $this->place_name;
        $web .= '<a href="' . $link . '">'. $this->place_name. '</a>';



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
        $text = strtoupper($this->place_name);

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
        imagestring($image, 2, $image_width-75, 10, $this->place_code, $textcolor);

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


	private function Respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "place";


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

        $this->thing_report['help'] = 'This is a Place.  The union of a code and a name.';

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

    function lastPlace() {

        $this->last_place = new Variables($this->thing, "variables place " . $this->from);
        $this->last_place_code = $this->last_place->getVariable('place_code');
        $this->last_place_name = $this->last_place->getVariable('place_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_place->getVariable('refreshed_at');
return;
        // So do it the hard way

        if (!isset($this->places)) {$this->getPlaces();}

        foreach(array_reverse($this->places) as $key=>$place) {

            if ($place['name'] == $this->last_place_name) {
                $this->last_refreshed_at = $place['refreshed_at'];
                break;
            }


        }

        //echo "last place refreshed at " .$this->last_refreshed_at;
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
            case ($this->agent_input != null):
                $input = strtolower($this->agent_input);
                break;
            case (true):
                // What is going to be found in from?
                // Allows place name extraction from email address.
                // Allows place code extraction from "from"
                // Internet of Things.  Sometimes all you have is the Thing's address.
                // And that's a Place.
                $input = strtolower($this->from . " " . $this->subject);
        }

        // Would normally just use a haystack.
        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
		// $haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractPlace($input);
        if ($this->agent_input == "extract") {return;}

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'place') {
                $this->getPlace();
                $this->response = "Last 'place' retrieved.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'next':
                            $this->thing->log("read subject nextheadcode");
        $this->nextPlace();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropPlace();
        break;

   case 'make':
   case 'new':
   case 'place':
   case 'create':
   case 'add':
        $this->assertPlace(strtolower($input));

        //$this->refreshed_at = $this->thing->time();

        if (empty($this->place_name)) {$this->place_name = "X";}

        $this->response = 'Asserted Place and found ' . strtoupper($this->place_name) .".";
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

    if ($this->place_code != null) {
        $this->getPlace($this->place_code);
        $this->thing->log($this->agent_prefix . 'using extracted place_code ' . $this->place_code . ".","INFORMATION");
        $this->response = $this->place_code . " used to retrieve a Place.";

        return;
    }

    if ($this->place_name != null) {

        $this->getPlace($this->place_name);

        $this->thing->log($this->agent_prefix . 'using extracted place_name ' . $this->place_name . ".","INFORMATION");
        $this->response = strtoupper($this->place_name) . " retrieved.";
$this->assertPlace($this->place_name);
        return;
    }

    if ($this->last_place_code != null) {
        $this->getPlace($this->last_place_code);
        $this->thing->log($this->agent_prefix . 'using extracted last_place_code ' . $this->last_place_code . ".","INFORMATION");
        $this->response = "Last place " . $this->last_place_code . " used to retrieve a Place.";

        return;
    }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

$place = strtolower($this->subject);

if ( !$this->getPlace(strtolower($place)) ){
    // Place was found
    // And loaded
    $this->response = $place . " used to retrieve a Place.";

    return;
}


        $this->makePlace(null, $place);
        $this->thing->log($this->agent_prefix . 'using default_place_code ' . $this->default_place_code . ".","INFORMATION");

        $this->response = "Made a Place called " . $place . ".";
        return;

        if (($this->isData($this->place_name)) or ($this->isData($this->place_code)) ) {
            $this->set();
            return;
        }

		return false;

	}

	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}

}

/* More on places

Lots of different ways to number places.

*/
?>
