<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


ini_set("allow_url_fopen", 1);

class Event 
{

    // This is a headcode.  You will probably want to read up about
    // the locomotive headcodes used by British Rail.

    // A headcode takes the form (or did in the 1960s),
    // of NANN.  Where N is a digit from 0-9, and A is an uppercase character from A-Z.

    // This implementation is recognizes lowercase and uppercase characters as the same.

    // The headcode is used by the Train agent to create the proto-train.

    // A headcode must have a route. Route is a text string.  Examples of route are:
    //  Gilmore > Hastings > Place
    //  >> Gilmore >>
    //  > Hastings

    // A headcode may have a consist. (Z - indicates train may fill consist. 
    // X - indicates train should specify the consist. (devstack: "Input" agent)
    // NnXZ is therefore a valid consist. As is "X" or "Z".  
    // A consist must always resolve to a locomotive.  Specified as uppercase letter.
    // The locomotive closest to the first character is the engine.  And gives 
    // commands to following locomotives to follow.

    // This is the headcode manager.  This person is pretty special.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) 
    {

        $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_name = "Event";
        $this->agent_prefix = 'Agent "Event" ';

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

        $this->keywords = array('event','next', 'accept', 'clear', 'drop','add','new');

//        $this->headcode = new Variables($this->thing, "variables headcode " . $this->from);

                $this->default_event_name = $this->thing->container['api']['event']['default_event_name'];
                $this->default_event_code = $this->thing->container['api']['event']['default_event_code'];


        // So around this point I'd be expecting to define the variables.
        // But I can do that in each agent.  Though there will be some
        // common variables?

        // So here is building block of putting a headcode in each Thing.
        // And a little bit of work on a common variable framework. 

        // Factor in the following code.

//                'headcode' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

                //$this->default_run_time = $this->thing->container['api']['headcode']['default run_time'];
                //$this->negative_time = $this->thing->container['api']['headcode']['negative_time'];

        // But for now use this below.

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.

        //$this->default_place_code = "090001";
        //$this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->json->time();

        // Loads in headcode variables.
        // This will attempt to find the latest head_code
//        $this->get(); // Updates $this->elapsed_time as well as pulling in the current headcode

        // Now at this point a  "$this->headcode_thing" will be loaded.
        // Which will be re-factored eventaully as $this->variables_thing.

        // This looks like a reminder below that the json time generator might be creating a token.

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

        //$this->thing->json->time()


        $this->verbosity = 1;

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

//        $this->thing->log('<pre> Agent "Headcode" running on Thing '. $this->thing->nuuid . '.</pre>');
//        $this->thing->log('<pre> Agent "Headcode" received this Thing "'.  $this->subject . '".</pre>');

//$split_time = $this->thing->elapsed_runtime();
//        $this->headcode = new Variables($this->thing, "variables headcode " . $this->from);
//        $this->head_code = $this->headcode->getVariable('head_code', null);

//$this->get();

//$this->thing->log( $this->agent_prefix .' set up variables in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.' );

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
//		$this->thing->log('<pre> Agent "Headcode" completed</pre>');


        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;



		return;

		}


    function set()
    {
//$this->head_code = "0Z15";
        $event = new Variables($this->thing, "variables event " . $this->from);

        $event->setVariable("event_code", $this->event_code);
        $event->setVariable("event_name", $this->event_name);
     //   $this->headcode->setVariable("index", $this->index);
        $event->setVariable("refreshed_at", $this->current_time);

$this->thing->log( $this->agent_prefix .' set ' . $this->event_code . ' and ' . $this->event_name . ".", "INFORMATION" );


        //$this->thing->json->writeVariable( array("headcode", "head_code"), $this->head_code );
        //$this->thing->json->writeVariable( array("headcode", "refreshed_at"), $this->current_time );

        $event = new Variables($this->thing, "variables " . $this->event_code . " " . $this->from);

        //$this->head_code = $this->headcode->getVariable("head_code");
        //$this->headcode->setVariable("consist", $this->consist);
        //$this->headcode->setVariable("run_at", $this->run_at);
        //$this->headcode->setVariable("quantity", $this->quantity);
        $event->setVariable("event_name", $this->event_name);



        return;
    }

    function nextEvent() {

        $this->thing->log("next event");
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

    function getEvent($selector = null)
    {
        foreach ($this->events as $event) {

            if (($event['code'] == $selector) or ($event['name'] == $selector)) {

                $this->event_name = $event['name'];
                $this->event_code = $event['code'];
                $this->event = new Variables($this->thing, "variables " . $this->event_code . " " . $this->from);
   
                return array($this->event_code, $this->event_name);
            }
       }
       //$this->event = true;
       return true;

    }

    function getEvents() {

        $this->eventcode_list = array();
        $this->eventname_list = array();
        $this->events = array();
        // See if a headcode record exists.
        $findagent_thing = new FindAgent($this->thing, 'event');

        $this->thing->log('Agent "Event" found ' . count($findagent_thing->thing_report['things']) ." event Things." );

//        if ($findagent_thing->thing_reports['things'] == false) {
//                $place_code = $this->default_place_code;
//                $place_name = $this->default_place_name;
//            return array($this->placecode_list, $this->placename_list, $this->places);

//        }
//var_dump($findagent_thing->thing_report['things']);
if ($findagent_thing->thing_report['things'] == true) {
    // No places found
} else {
        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            $thing= new Thing($uuid);
            $variables = $thing->account['stack']->json->array_data;

            if (isset($variables['event'])) {
             


//var_dump($place);
//exit();

                $event_code = $this->default_event_code;
                $event_name = $this->default_event_name;

                if(isset($variables['event']['event_code'])) {$event_code = $variables['event']['event_code'];}
                if(isset($variables['event']['event_name'])) {$event_name = $variables['event']['event_name'];}

                //$place_name = $variables['place']['place_name'];


                $this->events[] = array("code"=>$place_code, "name"=>$event_name);
                //$variables['place'][] = $thing_object['task'];
                $this->eventcode_list[] = $event_code;
                $this->eventname_list[] = $event_name;


            }
        }

}

        // Add in a set of default places

        $default_eventname_list = array("The Lion King", "They Came from Away", "Rent", "Hamilton", "The Book of Mormon");

        foreach ($default_eventname_list as $event_name) {
                $event_code = str_pad(RAND(1,9999999), 8, " ", STR_PAD_LEFT);

                $this->eventcode_list[] = $event_code;
                $this->eventname_list[] = $event_name;
                $this->events[] = array("code"=>$event_code, "name"=>$event_name);
       }

       // Indexing not implemented
        $this->max_index = 0;


        return array($this->eventcode_list, $this->eventname_list, $this->events);

    }

    private function get($event_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($event_code == null) {$event_code = $this->event_code;}

        $this->event = new Variables($this->thing, "variables " . $event_code . " " . $this->from);

        $this->event_code = $this->place->getVariable("event_code");
        $this->event_name = $this->place->getVariable("event_name");

        return array($this->event_code, $this->event_name);
    }

    function dropEvent() {
        $this->thing->log($this->agent_prefix . "was asked to drop an Event.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->event)) {
            $this->event->Forget();
            $this->event = null;
        }

        $this->get();
 
    }


    function makeEvent($event_code = null, $event_name = null) {
        //if ($place_code == null) {
        //    $place_code = $this->getVariable('place_code', $place_code);
        //}
//var_dump($place_code);
//exit();
        // Check is the place_code/place_name is allowed

        if (($event_code == null) or ($event_name == null)) {return true;}

        foreach ($this->events as $event) {
            if ($event_code == $event['code']) {return true;}

            if ($event_name == $event['name']) {return true;}
        }

        // Will be useful when devstack makePlace
        //$place_name = $this->getVariable('place_name', $place_name);


        $this->thing->log('Agent "Event" will make a Event for ' . $event_code . ".");


        $ad_hoc = true;

        if ( ($ad_hoc != false) ) {

            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $quantity = "X";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log($this->agent_prefix . "was told the Event is Useable but we might get kicked out.");

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;
//var_dump($place_code);

            //$this->get($place_code);

            if ($event_code == false) {
                $event_code = $this->default_event_code;
                $event_name = $this->default_event_name;
            }

            $this->current_event_code = $event_code;
            $this->event_code = $event_code;

            $this->current_event_name = $event_name;
            $this->event_name = $event_name;

            $this->set();

            $this->getEvents();
            $this->getEvent($this->event_code);

            $this->event_thing = $this->thing;

        }

//var_dump($this->place_code);
//var_dump($this->place_name);
//exit();
        // Need to code in the X and <number> conditions for creating new headcodes.

        // Write the variables to the db.
        //$this->set();

        //$this->headcode_thing = $this->thing;

        $this->thing->log('Agent "Event" found an Event and pointed to it.');

    }

    function eventTime($input = null) {

        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $event_time = "x";
            return $event_time;
        }


        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $event_time = $this->hour . $this->minute;

        if ($input == null) {$this->event_time = $event_time;}

        return $event_time;



    }

    public function extractEvents($input = null) {

        if (!isset($this->event_codes)) {
            $this->event_codes = array();
        }

        if (!isset($this->event_names)) {
            $this->event_names = array();
        }

//        $pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{7}$|";

        preg_match_all($pattern, $input, $m);
        $this->event_codes = $m[0];


        // Look for an established list of places.
        //$default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        //if (!isset($this->place_name_list)) {$this->get();}

        //$this->place_names = array();
        //foreach ($places as $place) {

            if (!isset($this->events)) {$this->getEvents();}

            foreach ($this->events as $event) {
                $event_name = strtolower($event['name']);
                $event_code = strtolower($event['code']);

//if ($place_name == null) {continue;}
//if ($place_code == null) {continue;}
//exit();
                // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
                if (strpos($input, $event_code) !== false)  {
                    $this->event_codes[] = $event_code;
                }

                if (strpos($input, $event_name) !== false)  {
                    $this->event_names[] = $event_name;
                }


            }



        //}

$this->event_codes = array_unique($this->event_codes);
$this->event_names = array_unique($this->event_names);


        return array($this->event_codes, $this->event_names);
    }

    public function extractEvent($input)

    {

        $this->event_name = null;
        $this->event_code = null;

        list($event_codes,$event_names) = $this->extractEvents($input);

        if ( ( count($event_codes) + count($event_names) ) == 1) {
            if (isset($event_codes[0])) {$this->event_code = $event_codes[0];}
            if (isset($event_names[0])) {$this->event_name = $event_names[0];}

            $this->thing->log( $this->agent_prefix  . 'found a event code (' . $this->event_code . ') in the text.');
            return array($this->event_code, $this->event_name);
        }

        return true;


        // And then extract place names.
        // Take out word 'place' at the start.
        $filtered_input = ltrim(strtolower($input), "event");

        foreach($this->event_names as $event_name) {

            if (strpos($filtered_input, $event_name) !== false) {
                $event_names[] = $event_name;
            }

        }

        if (count($event_names) == 1) {$this->event_name = $this->event_names[0];}

        return array($this->event_code, $this->event_name);
    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        //return $this->available;
    }



    function addEvent() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    function makeTXT()
    {
        if (!isset($this->eventcode_list)) {$this->getEvents();}
        $this->getEvents();

        if (!isset($this->event)) {$txt = 'No events found.';} else {

        $txt = 'These are EVENTS for RAILWAY ' . $this->event->nuuid . '. ';}
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
        foreach ($this->events as $key=>$event) {

            $txt .= " " . str_pad(strtoupper($event['name']), 40, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper($event['code']), 5, "X", STR_PAD_LEFT);
            

            $txt .= "\n";



        }




        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }

    function makeChoices () {

//        $this->thing->choice->Choose($this->state);
//        $this->thing->choice->save($this->keyword, $this->state);

        $node_list = array("event"=>array("going","meh"));

        $this->thing->choice->Create($this->agent_name, $node_list, "event");

        $this->choices = $this->thing->choice->makeLinks('event');
        $this->thing_report['choices'] = $this->choices;

    }


    private function makeSMS() {

        if ((!isset($this->event_name)) or ($this->event_name == null)) {
            $this->event_name = "None found";
            //$this->getEvent();
        }

        $sms_message = "EVENT";
// . strtoupper($this->event_code) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | ";

//var_dump($this->event_name);

        $sms_message .= $this->event_name;
 
//        $sms_message .= " | index " . $this->index;

//        $sms_message .= " | nuuid " . strtoupper($this->event->nuuid);
//        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

 //       $sms_message .= " | ptime " . number_format($this->event->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function Respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "event";


		//$choices = $this->thing->choice->makeLinks($this->state);
      //  $choices = false;
//		$this->thing_report['choices'] = $choices;

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
        $this->makeChoices();

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $this->choices['link'] . '].';
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
        $this->extractEvent($input);
        if ($this->agent_input == "extract") {return;}

//echo "extracted<br>";
//var_dump($this->event_name);
//var_dump($this->event_code);
//echo "<br>";
        // Return the current place

        $this->last_event = new Variables($this->thing, "variables event " . $this->from);
        $this->last_event_code = $this->last_event->getVariable('event_code');
        $this->last_event_name = $this->last_event->getVariable('event_name');
//var_dump($this->last_place->thing['uuid']);
//exit();
//echo "last_event_name<br>";
//var_dump($this->last_event_name);
//var_dump($this->last_event_code);
//echo "<br>";

//echo "event<br>";
//var_dump($this->event_name);
//var_dump($this->event_code);
//echo "<br>";

//exit();
        // If at this point we get false/false, then the default Place has not been created.
//        if ( ($this->place_code == false) and ($this->place_name == false) ) {
//            $this->makePlace($this->default_place_code, $this->default_place_name);
//        }


        //$this->get();
        
//exit();
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
        $this->nextEvent();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropEvent();
        break;

   case 'make':
   case 'new':
   case 'create':
   case 'add':
//exit();

        $event_type = "4";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);


        foreach (range(1,9999999) as $n) {
            foreach($this->events as $event) {
                $event_code = $event_type . str_pad($n, 6, "0", STR_PAD_LEFT);

                if ($this->getPlace($event_code)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log("No Event code available of type " . $event_type .".", "WARNING");
                return;
            }
        }

        if ($this->place_name == null) {$this->event_name = "Foo" . rand(0,1000000) . "Bar";}

        //$this->makeheadcode();
        $this->makePlace($this->event_code, $this->event_name);
        $this->getPlace($this->event_code);
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

// Check whether headcode saw a run_at and/or run_time
// Intent at this point is less clear.  But headcode
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time
//var_dump($this->last_event_code);
//var_dump($this->last_event_name);
//echo "<bR>";
//var_dump($this->event_code);
//var_dump($this->event_name);

if ($this->event_code != null) {
    $this->getEvent($this->event_code);
    $this->thing->log($this->agent_prefix . 'using extracted event_code ' . $this->event_code . ".","INFORMATION");
    return;
}

if ($this->event_name != null) {
    $this->getEvent($this->event_name);
    $this->thing->log($this->agent_prefix . 'using extracted event_name ' . $this->event_name . ".","INFORMATION");
    return;
}



if ($this->last_event_code != null) {
    $this->getEvent($this->last_event_code);
    $this->thing->log($this->agent_prefix . 'using extracted last_event_code ' . $this->last_event_code . ".","INFORMATION");
    return;}

//echo "dev";

$this->thing->log($this->agent_prefix . 'using default_event_code ' . $this->default_event_code . ".","INFORMATION");

$this->getEvent($this->default_event_code);

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
