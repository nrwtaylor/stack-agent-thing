<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Eventbrite
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        // $this->start_time = microtime(true);
        $this->start_time = $thing->elapsed_runtime();
        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->agent_prefix = 'Agent "Eventbrite" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('eventbrite', 'event','show','happening');

        $this->current_time = $this->thing->json->time();


        $this->api_key = $this->thing->container['api']['eventbrite'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "eventbrite" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

//        if ($this->verbosity == false) {$this->verbosity = 2;}


		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi();
        //if ($this->available_events_count > 10) {$this->getApi('date');}


		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'ran for ' . $milliseconds . 'ms.' );

		$this->thing->log( 'completed.');

        $this->thing_report['log'] = $this->thing->log;

		return;

	}



    function set()
    {
        $this->thing->log( $this->agent_prefix .  'set counter  ' . $this->counter . ".", "DEBUG");

        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log(' got counter ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;

        return;
    }





    function getApi($sort_order = null)
    {
        $this->thing->log('getApi answered.');

        if (isset($this->events)) {return $this->events;}

        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = str_replace(" ", "%20%", $keywords);

        $api_key = $this->api_key;
        $data_source = "https://www.eventbriteapi.com/v3/events/search/?token=". $api_key . "&q=vancouver";
        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Eventbrite.";
            $this->available_events_count = 0;
            $this->events = true;
            $this->events_count = 0;
            $this->thing->log( 'did not get any events.');

            return true;
            // Invalid query of some sort.
        }

//        $data_xml = simplexml_load_string($data);
        $json_data = json_decode($data, TRUE);

        // devstack
        // https://stackoverflow.com/questions/6167279/converting-a-simplexml-object-to-an-array
        //$events = json_decode(json_encode($data_xml), TRUE);
//var_dump($json_data);
//exit();
        $events = $json_data['events'];

        $this->eventsEventbrite($events);

        $this->available_events_count = count($this->events);

        $this->thing->log('getApi got ' . $this->available_events_count . " available events.");

        return false;

    }

function array_flatten(array $array)
{
    $flat = array(); // initialize return array
    $stack = array_values($array); // initialize stack
    while($stack) // process stack until done
    {
        $value = array_shift($stack);
        if (is_array($value)) // a value to further process
        {
            $stack = array_merge(array_values($value), $stack);
        }
        else // a value to take
        {
           $flat[] = $value;
        }
    }
    return $flat;
}


    function eventsEventbrite($events)
    {
        if (!isset($this->events)) {$this->events = array();}
        if($events == null) {$this->events_count = 0;return;}

        foreach($events as $not_used=>$event) {
//var_dump($event);
 //           $city = "vancouver";
//            if (strtolower($event['city']) != $city) {continue;}

            $id = $event['id'];

            $event_name = $event['name']['text'];

            $description = $event['description']['text'];
        // devstack extract dates from description
        // resolve multi-day events



            $run_at = $event['start']['local']; // local event time
            $end_at = $event['end']['local']; // local event time

        // runtime not available.  Perhaps that is what the full day flag tells people
            $runtime = strtotime($end_at) - strtotime($run_at);
            if ($runtime <= 0) {$runtime = "X";}

            //if ($runtime > $this->run_time_max) {echo "meep";continue;}


// Will need to run a venue request.
            $venue_name = null; //$event['venue_name'];

            $venue_address = null; //$event['venue_address'];

            if (is_array($event['url'])) {
                $link = null;
            } else {
                $link = $event['url'];
            }


            $event_array = array("event"=>$event_name, "runat"=>$run_at, "runtime"=>$runtime, "place"=>$venue_name, "link"=>$link, "datagram"=>$event);

            //$event_haystack = $this->implode_multi(" ", $event_array);
//var_dump($event_haystack);
            $pieces = $this->array_flatten($event_array, " ");
//var_dump($pieces);
  //          var_dump($this->search_words);

//            $keywords = explode(" ", $this->search_words);
//var_dump($this->search_words);

        if (!isset($this->search_words)) {
            $this->events[$id] = $event_array;
        } else {


            $keywords = explode(" ", $this->search_words);


        foreach ($pieces as $key=>$phrase) {
            $words = explode(" ",$phrase);
            foreach ($words as $piece) {
                foreach ($keywords as $command) {
//echo $command. " " . $piece . "\n";
//exit();
                    if (strpos(strtolower($piece),strtolower($command)) !== false) {
                        // Match found
                        $this->events[$id] = $event_array;
                    }
                }
            }
        }
        }
        }


        $this->events_count = count($this->events);

    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref; 
        return $this->link;
    }

    public function makeEvent($event)
    {
        throw new Exception('Under construction.');

        // Need to check whether the events exists...
        // This can be post response.   

        // devstack this will be an Event function
        // Just needs to pass the source to Event.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("eventbrite@stackr.ca","events", "s/ event eventbrite " . $eventful_id);

        // make sure the right fields are directly given

        new Event($thing, "event is ". $event['event']);
        new Runat($thing, "runat is ". $event['runat']);
        new Place($thing, "place is ". $event['place']);
        new Link($thing, "link is " . $event['link']);

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



    function getFlag() 
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag');
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }

    function setFlag($colour) 
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag '.$colour);
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }



	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "eventbrite";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;



        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);


        $this->flag = "green";

        $this->makeSms();
        $this->makeMessage();

        $this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportEventful();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This triggers provides currency prices using the 1forge API.';

//        $this->thingreportEventful();

		return;
	}

    public function eventString($event) 
    {
        $event_date = date_parse($event['runat']);

        $month_number = $event_date['month'];
        $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March

        $simple_date_text = $month_name . " " . $event_date['day'];
        $event_string = ""  . $simple_date_text;
        $event_string .= " "  . $event['event'];

        $runat = new Runat($this->thing, "extract ". $event['runtime']);


        $event_string .= " "  . $runat->day;
        $event_string .= " "  . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":"  . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);

        $run_time = new Runtime($this->thing, "extract " .$event['runtime']);

//var_dump($run_time->minutes);

        if ($event['runtime'] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " "  . $event['place'];

        return $event_string;
    }

    public function makeWeb()
    {
        if (!isset($this->search_words)) {$s = "";} else {$s = $this->search_words;}

        $html = "<b>EVENTBRITE " . $s . "</b>";
        $html .= "<p><b>Eventbrite Events</b>";

        if (!isset($this->events)) {$html .= "<br>No events found on Eventbrite.";} else {
 
        foreach ($this->events as $id=>$event) {

            $event_html = $this->eventString($event);
/*
            // Make a link to the Eventbrite page
            $link = "https://www.brownpapertickets.com/event/" . $id;
            $html_link = '<a href="' . $link . '">';
            $html_link .= "brown paper tickets";
            $html_link .= "</a>";

            $html_link_brownpapertickets = $html_link;
*/
            // Get event link. Normally an artist/performer link.
            $link = $event['link'];

            if ($link != null) {

                $scheme = parse_url($link, PHP_URL_SCHEME);
                if (empty($scheme)) {
                    $link = 'http://' . ltrim($link, '/');
                }

                $html_link_event = '<a href="' . $link . '">';
                $html_link_event .= "eventbrite";
                $html_link_event .= "</a>";
            } else {
                $html_link_event = "";
            }

            $html .= "<br>" . $event_html . " " . $html_link_event; // . " " . $html_link_brownpapertickets;

            }
        }
        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "EVENTBRITE";

        switch ($this->events_count) {
            case 0:
                $sms .= " | No events found.";
                break;
            case 1:
/*
                $event = reset($this->events);
                $event_date = date_parse($event['runat']);

                $month_number = $event_date['month'];
                $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March

                $simple_date_text = $month_name . " " . $event_date['day'];
*/
/*
                $sms .= " "  . $simple_date_text;

                $sms .= " "  . $event['event'];

                $runat = new Runat($this->thing, $event['runat']);

                $sms .= " "  . $runat->day;
                $sms .= " "  . $runat->hour;
                $sms .= ":"  . $runat->minute;

                $sms .= " "  . $event['place'];
*/
                $event = reset($this->events);
                $event_html = $this->eventString($event);
                $sms .= " | " .$event_html;


                if ($this->available_events_count != $this->events_count) {
                    $sms .= $this->events_count. " retrieved";
                }


                break;
            default:
                $sms .= " "  . $this->available_events_count . ' events ';
                if ($this->available_events_count != $this->events_count) {
                    $sms .= $this->events_count. " retrieved";
                }

                $event = reset($this->events);
                $event_html = $this->eventString($event);
                $sms .= " | " . $event_html;
        }

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        $message = "Eventbrite";

        switch ($this->events_count) {
            case 0:
                $message .= "did not find any events.";
                break;
            case 1:
                $event = reset($this->events);
                $event_html = $this->eventString($event);

                $message .= " found "  . $event_html . ".";

                //if ($this->available_events_count != $this->events_count) {
                //    $message .= $this->events_count. " events retrieved.";
                //}


                break;
            default:
                $message .= " found "  . $this->available_events_count . ' events.';
                //if ($this->available_events_count != $this->events_count) {
                //    $message .= $this->events_count. " retrieved";
                //}

                $event = reset($this->events);
                $event_html = $this->eventString($event);
                $message .= " This was one of them. " . $event_html .".";
        }

       // $message .= " | " . $this->response;

        // Really need to refactor this double :/

        $this->message = $message;

    }


    private function thingreportEventful()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function extractNumber($input = null)
    {
        if ($input == null) {$input = $this->subject;}

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }

        return $this->number;
    }

    public function readSubject()
    {
//        $this->response = "Asked Eventful about events.";
        $this->response = null;

        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

        $keywords = $this->keywords;

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }

        $this->input = $input;

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'eventbrite') {
                //$this->search_words = null;
                $this->response = "Asked Eventbrite about events.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

   case 'run':
   //     //$this->thing->log("read subject nextblock");
        $this->runTrain();
        break;

    default:
                                        }

                                }
                        }

                }


        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "eventbrite is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("eventbrite is")); 
        } elseif (($pos = strpos(strtolower($input), "eventbrite")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("eventbrite")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

    if ($filtered_input != "") {
        $this->search_words = $filtered_input;
        $this->response = "Asked Eventbrite about " . $this->search_words . " events";
        return false;
    }



        $this->response = "Message not understood";
		return true;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

       function discriminateInput($input, $discriminators = null) {


                //$input = "optout opt-out opt-out";

                if ($discriminators == null) {
                        $discriminators = array('accept', 'clear');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['accept'] = array('accept','add','+');
                $aliases['clear'] = array('clear','drop', 'clr', '-');



                $words = explode(" ", $input);

                $count = array();

                $total_count = 0;
                // Set counts to 1.  Bayes thing...     
                foreach ($discriminators as $discriminator) {
                        $count[$discriminator] = 1;

                       $total_count = $total_count + 1;
                }
                // ...and the total count.



                foreach ($words as $word) {

                        foreach ($discriminators as $discriminator) {

                                if ($word == $discriminator) {
                                        $count[$discriminator] = $count[$discriminator] + 1;
                                        $total_count = $total_count + 1;
                                                //echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                //echo "sum";
                                        }
                                }
                        }

                }

                //echo "total count"; $total_count;
                // Set total sum of all values to 1.

                $normalized = array();
                foreach ($discriminators as $discriminator) {
                        $normalized[$discriminator] = $count[$discriminator] / $total_count;            
                }


                // Is there good discrimination
                arsort($normalized);


                // Now see what the delta is between position 0 and 1

                foreach ($normalized as $key=>$value) {
                    //echo $key, $value;

                    if ( isset($max) ) {$delta = $max-$value; break;}
                        if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
                }


                        //echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}

?>

