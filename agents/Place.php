<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Place 
{

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

        $this->keywords = array('place','next', 'accept', 'clear', 'drop','add','new','here','there');

        $this->default_place_name = $this->thing->container['api']['place']['default_place_name'];
        $this->default_place_code = $this->thing->container['api']['place']['default_place_code'];

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


        // Read the subject to determine intent.

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
//$this->head_code = "0Z15";
        $place = new Variables($this->thing, "variables place " . $this->from);

        $place->setVariable("place_code", $this->place_code);
        $place->setVariable("place_name", $this->place_name);
     //   $this->headcode->setVariable("index", $this->index);
        $place->setVariable("refreshed_at", $this->current_time);

$this->thing->log( $this->agent_prefix .' set ' . $this->place_code . ' and ' . $this->place_name . ".", "INFORMATION" );


        //$this->thing->json->writeVariable( array("headcode", "head_code"), $this->head_code );
        //$this->thing->json->writeVariable( array("headcode", "refreshed_at"), $this->current_time );

        $place = new Variables($this->thing, "variables " . $this->place_code . " " . $this->from);

        //$this->head_code = $this->headcode->getVariable("head_code");
        //$this->headcode->setVariable("consist", $this->consist);
        //$this->headcode->setVariable("run_at", $this->run_at);
        //$this->headcode->setVariable("quantity", $this->quantity);
        $place->setVariable("place_name", $this->place_name);



        return;
    }

function isCode() {
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
                $place_code_candidate = str_pad(rand(100,9999) , 8, " ", STR_PAD_LEFT);
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
            // so this is where it doesn't do anything useful.
            // need to get places returning known relevant places

            if (($place['code'] == $selector) or ($place['name'] == $selector)) {

                $this->place_name = $place['name'];
                $this->place_code = $place['code'];
                $this->place = new Variables($this->thing, "variables " . $this->place_code . " " . $this->from);

                return array($this->place_code, $this->place_name);
            }

       }

       return true;

    }

    function getPlaces() {

        $this->placecode_list = array();
        $this->placename_list = array();
        $this->places = array();
        // See if a headcode record exists.
        $findagent_thing = new FindAgent($this->thing, 'place');

        $this->thing->log('Agent "Place" found ' . count($findagent_thing->thing_report['things']) ." place Things." );

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

                $place_code = $this->default_place_code;
                $place_name = $this->default_place_name;

                if(isset($variables['place']['place_code'])) {$place_code = $variables['place']['place_code'];}
                if(isset($variables['place']['place_name'])) {$place_name = $variables['place']['place_name'];}

                $this->places[] = array("code"=>$place_code, "name"=>$place_name);
                //$variables['place'][] = $thing_object['task'];
                $this->placecode_list[] = $place_code;
                $this->placename_list[] = $place_name;

            }
        }

}

        // Add in a set of default places

        $default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        foreach ($default_placename_list as $place_name) {
                $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

                $this->placecode_list[] = $place_code;
                $this->placename_list[] = $place_name;
                $this->places[] = array("code"=>$place_code, "name"=>$place_name); 
       }

       // Indexing not implemented
        $this->max_index = 0;


        return array($this->placecode_list, $this->placename_list, $this->places);

    }

    private function get($place_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($place_code == null) {$place_code = $this->place_code;}

        $this->place = new Variables($this->thing, "variables " . $place_code . " " . $this->from);

        $this->place_code = $this->place->getVariable("place_code");
        $this->place_name = $this->place->getVariable("place_name");

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


    function makePlace($place_code = null, $place_name = null) {

//        if (($place_code == null) or ($place_name == null)) {return true;}
        if ($place_name == null) {return true;}


//        if ($place_code == null) {$place_code = $this->nextCode();}


        // See if the code or name already exists
        foreach ($this->places as $place) {
            if ($place_code == $place['code']) {return true;}

            if ($place_name == $place['name']) {return true;}
        }

        if ($place_code == null) {$place_code = $this->nextCode();}


        // Will be useful when devstack makePlace
        //$place_name = $this->getVariable('place_name', $place_name);


        $this->thing->log('Agent "Place" will make a Place for ' . $place_code . ".");


        $ad_hoc = true;
        echo "ready to make a place<br>";
        if ( ($ad_hoc != false) ) {
            echo "making a place";
            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $quantity = "X";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log($this->agent_prefix . "was told the Place is Useable but we might get kicked out.");

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.

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

            $this->set();

            $this->getPlaces();
            $this->getPlace($this->place_code);

var_dump($this->place_name);
var_dump($this->place_code);

            $this->place_thing = $this->thing;

        }

//var_dump($this->place_code);
//var_dump($this->place_name);
//exit();
        // Need to code in the X and <number> conditions for creating new headcodes.

        // Write the variables to the db.
        //$this->set();

        //$this->headcode_thing = $this->thing;

        $this->thing->log('Agent "Place" found a Place and pointed to it.');

    }

    function placeTime($input = null) {

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

    public function extractPlaces($input = null) {

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



        //}

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


//echo $filtered_input;
//exit();


        if (count($place_names) == 1) {$this->place_name = $this->place_names[0];}
//var_dump($place_names);
        return array($this->place_code, $this->place_name);
    }
    function assertPlace($input) {


if (($pos = strpos(strtolower($input), "place is")) !== FALSE) { 
    $whatIWant = substr(strtolower($input), $pos+strlen("place is")); 
} elseif (($pos = strpos(strtolower($input), "place")) !== FALSE) { 
    $whatIWant = substr(strtolower($input), $pos+strlen("place")); 
}

$filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($this->getPlace($filtered_input)) {
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

    function makeTXT()
    {
        if (!isset($this->placecode_list)) {$this->getPlaces();}

        $this->getPlaces();

        if (!isset($this->place)) {$txt = "Not here";} else {

        $txt = 'These are PLACES for RAILWAY ' . $this->place->nuuid . '. ';
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

            $txt .= " " . str_pad(strtoupper($place['name']), 40, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper($place['code']), 5, "X", STR_PAD_LEFT);
            

            $txt .= "\n";



        }




        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    private function makeSMS() {

$s = "NOT USED";
        $sms_message = "PLACE " . strtoupper($this->place_code) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | ";

        $sms_message .= strtoupper($this->place_name);
 
//        $sms_message .= " | index " . $this->index;

//        $sms_message .= " | nuuid " . strtoupper($this->place->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

//        $sms_message .= " | ptime " . number_format($this->place->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function Respond() {

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

        $this->thing_report['help'] = 'This is a Place.  The union of a code and a name.';

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
        $input = strtolower($this->from . " " . $this->subject);
}

        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractPlace($input);
        if ($this->agent_input == "extract") {return;}


//        if (($this->place_name == null) or ($this->place_code == null)) {
//        }

        // Return the current place

        $this->last_place = new Variables($this->thing, "variables place " . $this->from);
        $this->last_place_code = $this->last_place->getVariable('place_code');
        $this->last_place_name = $this->last_place->getVariable('place_name');

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
        $this->nextPlace();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropPlace();
        break;

   case 'make':
   case 'new':
   case 'create':
   case 'place':
   case 'add':
//exit();

    
      //$this->nextCode();
        $this->assertPlace(strtolower($input));
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

    if ($this->place_code != null) {
        $this->getPlace($this->place_code);
        $this->thing->log($this->agent_prefix . 'using extracted place_code ' . $this->place_code . ".","INFORMATION");
        return;
    }

    if ($this->place_name != null) {
        $this->getPlace($this->place_name);
        $this->thing->log($this->agent_prefix . 'using extracted place_name ' . $this->place_name . ".","INFORMATION");
        return;
    }

    if ($this->last_place_code != null) {
        $this->getPlace($this->last_place_code);
        $this->thing->log($this->agent_prefix . 'using extracted last_place_code ' . $this->last_place_code . ".","INFORMATION");
        return;
    }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name
//var_dump($this->subject);
//echo $this->subject;
//$place = "dogpark";

$place = strtolower($this->subject);

if ( !$this->getPlace(strtolower($place)) ){
    // Place was found
    // And loaded
    return;
}


//    function makePlace($place_code = null, $place_name = null) {
$this->makePlace(null, $place);
$this->set();

$this->thing->log($this->agent_prefix . 'using default_place_code ' . $this->default_place_code . ".","INFORMATION");

//$this->getPlace($this->default_place_code);
//$this->getPlace($place);


            return;


    if (($this->isData($this->place_name)) or ($this->isData($this->place_code)) ) {
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

