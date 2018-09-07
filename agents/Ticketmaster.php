<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ticketmaster
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = microtime(true);

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


        $this->agent_prefix = 'Agent "Ticket Master" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('ticket master','event','show','happening');

        $this->current_time = $this->thing->json->time();


        $this->consumer_key = $this->thing->container['api']['ticket_master']['consumer_key'];
        $this->consumer_secret = $this->thing->container['api']['ticket_master']['consumer_secret'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "ticket_master" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

//        if ($this->verbosity == false) {$this->verbosity = 2;}


		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi("popularity");
        if ($this->available_events_count > 10) {$this->getApi('date');}


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
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;

        return;
    }





    function getApi($sort_order = null)
    {

        if ($sort_order == null) {$sort_order = "popularity";}
        // http://api.eventful.com/docs/events/search

        // https://developer.picatic.com/v2/api/#methods-event-update
        // https://api.picatic.com/v2/event/:id

        // 'authorization: Bearer sk_live_xxxxxxxxxxxxxxxxxxxxxxxx'

        // 'https://api.picatic.com/v2/event?filter\[user_id\]=5533&page\[limit\]=2&page\[offset\]=0'


        //count_only boolean
        //    If count_only is set, an abbreviated version of the output will be returned. Only total_items and search_time elements are included in the result. (optional) 

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = str_replace(" ", "%20%", $keywords);

        $data_source = "https://app.ticketmaster.com/discovery/v2/events.json?size=1&keyword=" . $keywords  . "&city=vancouver&countryCode=CA&apikey=" . $this->consumer_key;
        if ($keywords == "") {
            $data_source = "https://app.ticketmaster.com/discovery/v2/events.json?size=1&city=vancouver&countryCode=CA&apikey=" . $this->consumer_key;
        }

//        $data_source = "https://api.picatic.com/v2/event";

        //echo $data_source;
        //$data_source = "http://api.eventful.com/json/events/search?app_key=" . $this->api_key . "&location=" . $city . "&keywords=" . $keywords . "&sort_order=popularity&count_only=true";


        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        //$data = file_get_contents($data_source, false, $context);
        $data = @file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Ticketmaster.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);
var_dump($json_data);
// devstack here on
return;

$total_items = $json_data['total_items'];
$page_number = $json_data['page_number'];
$page_count = $json_data['page_count'];
$page_size = $json_data['page_size'];

$search_time = $json_data['search_time'];

$this->thing->log('says Picatic reported a runtime of ' . $search_time . "?");

$this->thing->log('read page ' . $page_number . " of " . $page_count . " pages.");
$this->thing->log('read page ' . $page_size . " of " . $total_items . " Event things.");

$this->available_events_count = $total_items;

$eventful_json = $json_data['events'];

$eventful_events = $eventful_json['event'];

$this->eventsEventful($eventful_events);
return false;

    }


    function eventsEventful($eventful_events)
    {
        if (!isset($this->events)) {$this->events = array();}
        if($eventful_events == null) {$this->events_count = 0;return;}

foreach($eventful_events as $id=>$event) {
//var_dump($id);
    $eventful_event = $event;
//var_dump($event);
//exit();
    $privacy = $eventful_event['privacy'];
if ($privacy != 1) {echo "privacy";continue;}

    $region_abbr = $eventful_event['region_abbr'];

if ($region_abbr != "BC") {echo "bc";continue;} // Restrict to BC events in dev/test/prod

    $all_day_flag = $eventful_event['all_day'];



    $description = $eventful_event['description'];

    // devstack extract dates from description
    // resolve multi-day events


/* Not today but good to learn performer names
    $eventful_performers = $eventful_event['performers'];
    $eventful_performer_names = array();
    if ($eventful_performers != null) {
        foreach($eventful_performers['performer'] as $id=>$eventful_performer) {
            $eventful_performer_names[] = $eventful_performer['name'];
        }
    }
*/

    $eventful_venue_id = $eventful_event['venue_id'];

    $eventful_title = $eventful_event['title'];

    $eventful_id = $eventful_event['id'];

    $run_at = $eventful_event['start_time']; // local event time

    $end_at = $eventful_event['stop_time']; // local event time


    // runtime not available.  Perhaps that is what the full day flag tells people
    $runtime = strtotime($end_at) - strtotime($run_at);
    if ($runtime <= 0) {$runtime = "X";}

    if ($runtime > $this->run_time_max) {$continue;}

    $venue_name = $eventful_event['venue_name'];

    $all_day_flag = $eventful_event['all_day'];

    $eventful_address = $eventful_event['venue_address'];


    $link = $eventful_event['url'];
//var_dump($eventful_event);
//exit();

//    $runtime = $this->thing->human_time($runtime);

  //  echo $eventful_title . " " .$runtime . "\n";
    $this->events[$eventful_id] = array("event"=>$eventful_title, "runat"=>$run_at, "runtime"=>$runtime, "place"=>$venue_name, "link"=>$link);

}      


$this->events_count = count($this->events);



    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref; 
        return $this->link;
    }

    public function makeEvent()
    {



    // Need to check whether the events exists...
    // This can be post response.   


    // Load as new event things onto stack
    $thing = new Thing(null);
//    $thing->Create("eventful@stackr.ca","events", "event is eventful #" . $eventful_id);
    $thing->Create("eventful@stackr.ca","events", "s/ event eventful " . $eventful_id);

    // make sure the right fields are directly given

    new Event($thing, "event is ". $eventful_title);
    new Runat($thing, "runat is ". $run_at);
    new Place($thing, "place is ". $venue_name);
    new Link($thing, "link is " . $link);






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
		$from = "eventful";

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

        $runat = new Runat($this->thing, $event['runat']);

        $event_string .= " "  . $runat->day;
        $event_string .= " "  . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":"  . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);

//var_dump($event['runtime']);

$run_time = new Runtime($this->thing, $event['runtime']);
//var_dump($run_time->minutes);

if ($event['runtime'] != "X") {
    $event_string .= " " . $this->thing->human_time($run_time->minutes);
}
//var_dump($event_string);
//        $event_string .= " "  . $endat->day;
//        $event_string .= " "  . $endat->hour;
//        $event_string .= ":"  . $endat->minute;

                $event_string .= " "  . $event['place'];


        return $event_string;

    }

    public function makeWeb()
    {
        $html = "<b>TICKETMASTER</b>";
        $html .= "<p><b>Ticket Master Events</b>";

        if (!isset($this->events)) {$html .= "<br>No events found on Ticket Master.";} else {
        
        foreach ($this->events as $id=>$event) {
            //var_dump($event['event']);
            //var_dump($event['runat']);
            //var_dump($event['place']);
            //var_dump($event['link']);
//            $event_html = $event['event'] . " " . $event['runat'] . " " . $event['place'];

            $event_html = $this->eventString($event);

//        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';
        $link = $event['link'];
        $html_link = '<a href="' . $link . '">';
//        $web .= $this->html_image;
        $html_link .= "ticketmaster";
        $html_link .= "</a>";

            $html .= "<br>" . $event_html . " " . $html_link;
            //exit();
        }
        }

        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "TICKETMASTER";

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
        $message = "Ticketmaster";

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

            if ($input == 'ticketmaster') {
                //$this->search_words = null;
                $this->response = "Asked Ticket Master about events.";
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
        if (($pos = strpos(strtolower($input), "ticketmaster is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("ticketmaster is")); 
        } elseif (($pos = strpos(strtolower($input), "ticketmaster")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("ticketmaster")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

    if ($filtered_input != "") {
        $this->search_words = $filtered_input;
        $this->response = "Asked Ticket Master about " . $this->search_words . " events";
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

