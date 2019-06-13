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


        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->keywords = array('event','next', 'accept', 'clear', 'drop','add','new');

//        $this->headcode = new Variables($this->thing, "variables headcode " . $this->from);

                $this->default_event_name = $this->thing->container['api']['event']['default_event_name'];
                $this->default_event_code = $this->thing->container['api']['event']['default_event_code'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';



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
        $this->lastEvent();
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

        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

    }


    function set()
    {

        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}
        //$this->refreshed_at = $this->current_time;
        $event = new Variables($this->thing, "variables event " . $this->from);

        $event->setVariable("event_code", $this->event_code);
        $event->setVariable("event_name", $this->event_name);

        $event->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log( $this->agent_prefix .' set ' . $this->event_code . ' and ' . $this->event_name . ".", "INFORMATION" );

        $event = new Variables($this->thing, "variables " . $this->event_code . " " . $this->from);
        $event->setVariable("event_name", $this->event_name);
        $event->setVariable("refreshed_at", $this->refreshed_at);


        return;
    }
/*


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
*/
    function lastEvent()
    {
        $this->last_event = new Variables($this->thing, "variables event " . $this->from);
        $this->last_event_code = $this->last_event->getVariable('event_code');
        $this->last_event_name = $this->last_event->getVariable('event_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_event->getVariable('refreshed_at');
        return;
    }


    function assertEvent($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "event is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("event is")); 
        } elseif (($pos = strpos(strtolower($input), "event")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("event")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $event = $this->getEvent($filtered_input);
        if ($event) {
            //true so make a place
            $this->makeEvent(null, $filtered_input);
        }

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
/*
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
*/

    function getEvent($selector = null)
    {
        foreach ($this->events as $event) {
            // Match the first matching place
            if (($selector == null) or ($selector == "")) {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->event_name = $this->last_event_name;
                $this->event_code = $this->last_event_code;

                $this->getRuntime();
                $this->getRunat();

                //$this->day = "X";
                //$this->minutes = "X";
                //$this->hour = "X";
                //$this->minute = "X";

                $this->event = new Variables($this->thing, "variables " . $this->event_code . " " . $this->from);
                return array($this->event_code, $this->event_name);
            }

            if (($event['code'] == $selector) or ($event['name'] == $selector)) {
                $this->refreshed_at = $event['refreshed_at'];
                $this->event_name = $event['name'];
                $this->event_code = $event['code'];

                // Get the most recent value (that isn't X)
                if ((!isset($this->day)) or ($this->day == "X")) {$this->day = $event['day'];}
                if ((!isset($this->minutes)) or ($this->minutes == "X")) {$this->minutes = $event['minutes'];}
                if ( ( (!isset($this->hour)) or (!isset($this->minute)) ) or ( ($this->hour == "X") or ($this->minute == "X") ) ) {
                $this->hour = $event['hour'];
                $this->minute = $event['minute'];
                }



                $this->event = new Variables($this->thing, "variables " . $this->event_code . " " . $this->from);
                return array($this->event_code, $this->event_name);
            }
       }

       return true;
    }

    function getEvents()
    {
        $this->eventcode_list = array();
        $this->eventname_list = array();
        $this->events = array();

        // See if a headcode record exists.
        //$findagent_thing = new Findagent($this->thing, 'event');
        $findagent_thing = new FindAgent($this->thing, 'event'); //prod

        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Event" found ' . count($findagent_thing->thing_report['things']) ." event Things." );

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

                if (isset($variables['event'])) {

                    $event_code = $this->default_event_code;
                    $event_name = $this->default_event_name;
                    $refreshed_at = "meep getEvents";

                    if(isset($variables['event']['event_code'])) {$event_code = $variables['event']['event_code'];}
                    if(isset($variables['event']['event_name'])) {$event_name = $variables['event']['event_name'];}
                    if(isset($variables['event']['refreshed_at'])) {$refreshed_at = $variables['event']['refreshed_at'];}

                    if(isset($variables['runtime']['minutes'])) {$minutes = $variables['runtime']['minutes'];} else {$minutes = "X";}
                    if(isset($variables['runat']['day'])) {$day = $variables['runat']['day'];} else {$day = "X";}

                    if(isset($variables['runat']['hour'])) {$hour = $variables['runat']['hour'];} else {$hour = "X";}
                    if(isset($variables['runat']['minute'])) {$minute = $variables['runat']['minute'];} else {$minute = "X";}

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

                        $this->events[] = array("code"=>$event_code,
                                                "name"=>$event_name,
                                                "refreshed_at"=>$refreshed_at,
                                                "minutes"=>$minutes,
                                                "day"=>$day,
                                                "hour"=>$hour,
                                                "minute"=>$minute
                                                );
                        $this->eventcode_list[] = $event_code;
                        $this->eventname_list[] = $event_name;
  //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_events = array();
        foreach(array_reverse($this->events) as $key=>$event) {

            $event_name = $event['name'];
            $event_code = $event['code'];

            $minutes = $event['minutes'];
            $day = $event['day'];
            $hour = $event['hour'];
            $minute = $event['minute'];



            if (!isset($event['refreshed_at'])) {continue;}

            $refreshed_at = $event['refreshed_at'];

            if (isset($filtered_events[$event_name]['refreshed_at'])) {
                if (strtotime($refreshed_at) > strtotime($filtered_events[$event_name]['refreshed_at'])) {
                    $filtered_events[$event_name] = array("name"=>$event_name,
                                                            "code"=>$event_code, 
                                                            'refreshed_at' => $refreshed_at,
                                                            "minutes"=>$minutes,
                                                            "day"=>$day,
                                                            "hour"=>$hour,
                                                            "minute"=>$minute
                                                            );
                }
                continue;
            } 

            $filtered_events[$event_name] = array("name"=>$event_name,
                                                            "code"=>$event_code,
                                                            'refreshed_at' => $refreshed_at,
                                                            "day"=>$day,                                 
                                                            "minutes"=>$minutes,
                                                            "hour"=>$hour,
                                                            "minute"=>$minute 
                                                            );



        }

$refreshed_at = array();
foreach ($this->events as $key => $row)
{
    $refreshed_at[$key] = $row['refreshed_at'];
}
array_multisort($refreshed_at, SORT_DESC, $this->events);

/*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
$this->old_events = $this->events;
$this->events = array();
foreach ($this->old_events as $key =>$row)
{

//var_dump( strtotime($row['refreshed_at']) );
    if ( strtotime($row['refreshed_at']) != false) {
      $this->events[] = $row;
    }
}

//exit();
    //exit();

        // Add in a set of default places
         $file = $this->resource_path .'event/events.txt';
         $contents = file_get_contents($file);
        $handle = fopen($file, "r");


        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $event_name = $line;
                // This is where the place index will be called.
                $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);

                $this->eventcode_list[] = $event_code;
                $this->eventname_list[] = $event_name;
                $this->events[] = array("code"=>$event_code,
                                            "name"=>$event_name,
                                            "refreshed_at"=>$this->start_time,
                                                "minutes"=>$minutes,
                                                "day"=>$day,
                                                "hour"=>$hour,
                                                "minute"=>$minute
                                            ); 

            }

            fclose($handle);
        } else {
            // error opening the file.
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

        $this->event_code = $this->event->getVariable("event_code");
        $this->event_name = $this->event->getVariable("event_name");
      $this->refreshed_at = $this->event->getVariable("refreshed_at");

        return array($this->event_code, $this->event_name);

    }

    private function getRuntime()
    {
        //if (isset($this->minutes)) {return;}

        $agent = new Runtime($this->thing, "runtime");
        $this->minutes = $agent->minutes;


    }

    private function getRunat()
    {

        $agent = new Runat($this->thing, "runat");

        if (isset($agent->day)) {$this->day = $agent->day;}
        if (isset($agent->hour)) {$this->hour = $agent->hour;}
        if (isset($agent->minute)) {$this->minute = $agent->minute;}



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


    function nextCode()
    {
/*
        $event_code_candidate = null;

        foreach ($this->events as $event) {
            $event_code = strtolower($event['code']);
            if (($event_code == $event_code_candidate) or ($event_code_candidate == null)) {
                $event_code_candidate = str_pad(rand(1000000,9999999) , 8, "9", STR_PAD_LEFT);
                $event_code_candidate = str_pad(rand(1000000,9999999) , 8, "9", STR_PAD_LEFT);

            }
        }
*/

//$event_code = rand(1000000,9999999);
//if $this->eventExists($event_code);

$x = 0;
while($x <= 50) {
    $event_code = rand(1000000,9999999);
    if ($this->eventExists($event_code)) {continue;} else {break;}

    $x++;
} 

        return $event_code;

    }

    function eventExists($event_candidate)
    {

        foreach ($this->events as $event) {

            $event_code = strtolower($event['code']);
            $event_name = strtolower($event['name']);

            if (($event_code == $event_candidate) or ($event_candidate == null) or ($event_name == $event_candidate)) {
                return true;
            }
        }
        return false;

    }


    function makeEvent($event_code = null, $event_name = null)
    {
        if ($event_name == null) {return true;}

        // See if the code or name already exists
        foreach ($this->events as $event) {
            if (($event_code == $event['code']) or ($event_name == $event['name'])) {
                $this->event_name = $event['name'];
                $event_code =$event['code'];
                $this->last_refreshed_at = $event['refreshed_at'];
            }
        }
        if ($event_code == null) {$event_code = $this->nextCode();}

        $this->thing->log('Agent "Event" will make an Event for ' . $event_code . ".");

        $ad_hoc = true;
        $this->thing->log($this->agent_prefix . "is ready to make an Event.");
        if ( ($ad_hoc != false) ) {
            $this->thing->log($this->agent_prefix . "is making an Event.");
            $this->thing->log($this->agent_prefix . "was told the Event is okay but we might get kicked out.");

            // An event needs a runtime and a runat
            $runtime = new Runtime($this->thing, "extract");
            $runat = new Runat($this->thing, "extract");

            $this->minutes = $runtime->minutes;
            $this->day = $runat->day;
            $this->hour = $runat->hour;
            $this->minute = $runat->minute;

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($event_code == false) {
                $event_code = $this->default_event_code;
                $event_name = $this->default_event_name;
            }

            $this->current_event_code = $event_code;
            $this->event_code = $event_code;

            $this->current_event_name = $event_name;
            $this->event_name = $event_name;
            $this->refreshed_at = $this->current_time;
  
            // This will write the refreshed at.
            $this->set();

            $this->getEvents();
            $this->getEvent($this->event_code);

            $this->event_thing = $this->thing;
        }
        $this->thing->log('Agent "Event" found an Event and pointed to it.');

    }

    function eventTime($input = null)
    {
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

            if (empty($event_name)) {continue;}
            if (empty($event_code)) {continue;}

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
//        $filtered_input = ltrim(strtolower($input), "event");

//        foreach($this->event_names as $event_name) {

 //           if (strpos($filtered_input, $event_name) !== false) {
                $event_names[] = $event_name;
//            }

//        }

        if (count($event_names) == 1) {$this->event_name = $this->event_names[0];}

        return array($this->event_code, $this->event_name);
    }

   function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/event.txt';

        $this->node_list = array("event"=>array("translink", "job"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "event");
        $choices = $this->thing->choice->makeLinks('event');


        $web = '<b>Event Agent</b><br>';
        $web .= "This agent manages a uniquely numbered live event via text message.<br>";

/*
        $web .= '<a href="' . $link . '">';

// Get aan html image if there is one if (!isset($this->html_image)) {
if (!isset($this->html_image)) {
    if (function_exists("makePNG")) {
        $this->makePNG();
    } else {
        $this->html_image = true;
    }
}

//$this->makePNG();
        $web .= $this->html_image;


$web .= "<br>";

        $web .= "</a>";
        $web .= "<br>";
*/
        $web .= "<br>event_name is " . $this->event_name . "";
        $web .= "<br>event_code is " . $this->event_code . "";

        $web .= "<br>run_time is " . $this->minutes . " minutes";
        $web .= "<br>run_at is " . $this->day . " " . $this->hour . " " .$this->minute;



        $web .= "<br>sms message is ";
        $web .= $this->sms_message;
        $web .= "<br>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/event.txt';
        $web .= '<a href="' . $link . '">event.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/event.log';
        $web .= '<a href="' . $link . '">event.log</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/'. $this->event_name;
        $web .= '<a href="' . $link . '">'. $this->event_name. '</a>';

        $web .= "<br>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->refreshed_at) );
        $web .= "Last asserted about ". $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
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

            $txt .= " " . str_pad(strtoupper($event['name']), 20, " ", STR_PAD_RIGHT);
            $txt .= " " . "  " .str_pad(strtoupper($event['code']), 5, "X", STR_PAD_LEFT);
            $txt .= " " . "  " .str_pad(strtoupper($event['minutes']), 5, "X", STR_PAD_LEFT);
            $txt .= " " . "  " .str_pad(strtoupper($event['day']), 5, "X", STR_PAD_LEFT);
            $txt .= " " . "  " .str_pad(strtoupper($event['hour']), 5, "X", STR_PAD_LEFT);
            $txt .= " " . "  " .str_pad(strtoupper($event['minute']), 5, "X", STR_PAD_LEFT);


//           $this->minutes = $runtime->minutes;
//            $this->day = $runat->day;
//            $this->hour = $runat->hour;
//            $this->minute = $runat->minute;



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
//$sms_message .= $this->event->nuuid ." ";
// . strtoupper($this->event_code) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " ";

//var_dump($this->event_name);
        $sms_message .= $this->event_name;

        $event_code_text = "X";
        if ($this->event_code != false) {
            $event_code_text = trim($this->event_code);
        }
        $sms_message .= " " . $event_code_text;


        $minutes_text = "Set RUNTIME. ";
        if ( (isset($this->minutes)) and ($this->minutes != false)) {
            $minutes_text = "runtime " . $this->minutes . " minutes. ";
        }
        $sms_message .= " | " . $minutes_text;


        $run_at_text = "Set RUNAT. ";

        if ( (isset($this->day)) or (isset($this->hour)) or (isset($this->minute)) ) { 


        if ( (isset($this->hour)) and (isset($this->minute)) ) { 
            $run_at_text .= " "; 
$hour_text = str_pad($this->hour,2, "0",STR_PAD_LEFT);
$minute_text = str_pad($this->minute,2,"0",STR_PAD_LEFT);
$day_text = $this->day;


            $run_at_text = "runat " . $hour_text . ":" . $minute_text . " " . $day_text . " ";} 
        }

//        if ( (isset($this->day)) and (isset($this->minute)) ) { 
//            $run_at_text .= " "; 
//            if (isset($this->day)) {$sms_message .= $this->day;} 
//        }

        if ( ($this->day == "X") or ($this->hour == "X") or ($this->minute == "X" )) { 
            $run_at_text = "Set RUNAT. ";
        }


        $sms_message .= $run_at_text;



//        $sms_message .= " | index " . $this->index;

//        $sms_message .= " | nuuid " . strtoupper($this->event->nuuid);
//        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

 //       $sms_message .= " | ptime " . number_format($this->event->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function Respond()
    {
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

        $this->makeWeb();
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

        if ($this->agent_input == "vancouver pride 2018") {
            $this->makeEvent(500001,"vancouver pride 2018");
            echo $this->event_name;
            return;
        }


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


        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractEvent($input);
        if ($this->agent_input == "extract") {return;}

        if (count($pieces) == 1) {
            if ($input == 'event') {
                $this->getEvent();

$this->getRunat();
$this->getRuntime();

//$this->getRunat();
//$this->getRuntime();
                $this->response = "Last 'event' retrieved.";
                return;
            }
        }


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
   case 'event':
        $this->assertEvent(strtolower($input));

        //$this->refvar_dump($this->minute);
//reshed_at = $this->thing->time();
$this->getRunat();
$this->getRuntime();
//var_dump($this->day);
//var_dump($this->hour);
//var_dump($this->minute);
//exit();

        if (empty($this->event_name)) {$this->event_name = "X";}

        $this->response = 'Asserted Event and found ' . strtoupper($this->event_name) .".";
        return;
        break;


        $event_type = "4";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);


        foreach (range(1,9999999) as $n) {
            foreach($this->events as $event) {
                $event_code = $event_type . str_pad($n, 6, "0", STR_PAD_LEFT);

                if ($this->getEvent($event_code)) {
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
        $this->makeEvent($this->event_code, $this->event_name);
        $this->getEvent($this->event_code);
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
        $this->response = $this->event_code . " used to retrieve an Event.";

        return;
    }

    if ($this->event_name != null) {

        $this->getEvent($this->event_name);

        $this->thing->log($this->agent_prefix . 'using extracted event_name ' . $this->event_name . ".","INFORMATION");
        $this->response = strtoupper($this->event_name) . " retrieved.";
$this->assertEvent($this->event_name);
        return;
    }

    if ($this->last_event_code != null) {
        $this->getEvent($this->last_event_code);
        $this->thing->log($this->agent_prefix . 'using extracted last_event_code ' . $this->last_event_code . ".","INFORMATION");
        $this->response = "Last event " . $this->last_event_code . " used to retrieve an Event.";

        return;
    }


        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

$event = strtolower($this->subject);

if ( !$this->getEvent(strtolower($event)) ){
    // Event was found
    // And loaded
    $this->response = $event . " used to retrieve an Event.";

    return;
}


        $this->makeEvent(null, $event);
        $this->thing->log($this->agent_prefix . 'using default_event_code ' . $this->default_event_code . ".","INFORMATION");

        $this->response = "Made an Event called " . $event . ".";
        return;





//$this->getEvent($this->default_event_code);

            return;



    if (($this->isData($this->place_name)) or ($this->isData($this->place_code)) ) {
        $this->set();
        return;
    }

    $this->read();




                return "Message not understood";




		return false;

	
	}

    function is_positive_integer($str)
    {
        return (is_numeric($str) && $str > 0 && $str == round($str));
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
