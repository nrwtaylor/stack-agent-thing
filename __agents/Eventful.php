<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Eventful 
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
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


        // Get some stuff from the stack which will be helpful.
        $this->state = $thing->container['stack']['state'];

        $this->agent_prefix = 'Agent "Eventful" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('eventful','event','show','happening');

        $this->current_time = $this->thing->json->time();


        $this->api_key = $this->thing->container['api']['eventful'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "eventful" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

//        if ($this->verbosity == false) {$this->verbosity = 2;}


		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

//        $this->getEventful("popularity");
//        if ($this->available_events_count > 10) {$this->getEventful('date');}

        // is this production
        if ($this->state != 'prod') {
            $this->response = "Asked Eventful about " . $this->search_words . " events.";
            $this->getEventful("popularity");
            if ($this->available_events_count > 10) {$this->getEventful('date');}
        } else {
            $this->response = "Eventful not licensed for production.";
        }



		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'ran for ' . $milliseconds . 'ms.' );

		$this->thing->log( 'completed.');
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );


        $this->thing_report['log'] = $this->thing->log;

		return;

	}

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

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

    function getEventful($sort_order = null)
    {

        if ($sort_order == null) {$sort_order = "popularity";}

        //count_only boolean
        //    If count_only is set, an abbreviated version of the output will be returned. Only total_items and search_time elements are included in the result. (optional) 

        // devstack create City agent
        //$city = "vancouver";
        $c = new City($this->thing,"city");
        $city = $c->city_name;
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        $data_source = "http://api.eventful.com/json/events/search?app_key=" . $this->api_key . "&mature=" . "safe" . "&location=" . $city . "&keywords=" . $keywords . "&sort_order=" . $sort_order;

        //$data_source = "http://api.eventful.com/json/events/search?app_key=" . $this->api_key . "&location=" . $city . "&keywords=" . $keywords . "&sort_order=popularity&count_only=true";

        $data = @file_get_contents($data_source);
        if ($data == false) {
            $this->response = "Could not ask Eventful.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        $total_items = $json_data['total_items'];
        $page_number = $json_data['page_number'];
        $page_count = $json_data['page_count'];
        $page_size = $json_data['page_size'];

        $search_time = $json_data['search_time'];

        $this->thing->log('says Eventful reported a runtime of ' . $search_time . "?");

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
            $eventful_event = $event;

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

            $this->events[$eventful_id] = array("event"=>$eventful_title, "runat"=>$run_at, "runtime"=>$runtime, "place"=>$venue_name, "link"=>$link);

        }

        $this->events_count = count($this->events);

    }

    function getLink($ref)
    {
        // Give it the message returned from the API service
        $this->link = "https://www.eventful.com"; 
        return $this->link;
    }

    public function makeEvent()
    {

        // Need to check whether the events exists...
        // This can be post response.   


        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("eventful@stackr.ca","events", "s/ event eventful " . $eventful_id);

        // make sure agents are explicity asked to help track this event
        new Event($thing, "event is ". $eventful_title);
        new Runat($thing, "runat is ". $run_at);
        new Place($thing, "place is ". $venue_name);
        new Link($thing, "link is " . $link);

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

	private function respond()
    {
		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "eventful";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

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

        $this->thing_report['help'] = 'This provides events from the Eventful API.';

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

        $runat = new Runat($this->thing, "extract " . $event['runat']);

        $event_string .= " "  . $runat->day;
        $event_string .= " "  . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":"  . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);

        $run_time = new Runtime($this->thing, "extract " .$event['runtime']);

        if ($event['runtime'] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " "  . $event['place'];

        return $event_string;
    }

    public function makeWeb()
    {
        $html = "<b>EVENTFUL</b>";
        $html .= "<p><b>Eventful Events</b>";

        if (!isset($this->events)) {
            $html .= "<br>No events found on Eventful.";
        } else {

            foreach ($this->events as $id=>$event) {

                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                $html_link .= "eventful";
                $html_link .= "</a>";

                $html .= "<br>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "EVENTFUL";

        if (!isset($this->events_count)) {
            $events_count = 0;
        } else {
            $events_count = $this->events_count;
        }

        switch ($events_count) {
            case true:
                $sms .= " | Request not made.";
                break;
            case 0:
                $sms .= " | No events found.";
                break;
            case 1:
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
        $message = "Eventful";

        if (!isset($this->events_count)) {
            $events_count = 0;
        } else {
            $events_count = $this->events_count;
        }


        switch ($events_count) {
            case 0:
                $message .= " did not find any events.";
                break;
            case 1:
                $event = reset($this->events);
                $event_html = $this->eventString($event);

                $message .= " found "  . $event_html . ".";

                break;
            default:
                $message .= " found "  . $this->available_events_count . ' events.';

                $event = reset($this->events);
                $event_html = $this->eventString($event);
                $message .= " This was one of them. " . $event_html .".";

        }

        $this->message = $message;
    }


    private function thingreportEventful()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
        $this->response = null;

        $this->num_hits = 0;

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

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'eventful') {
                //$this->search_words = null;
                $this->response = "Eventful requested.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                    default:
                      }
                }
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "eventful is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("eventful is")); 
        } elseif (($pos = strpos(strtolower($input), "eventful")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("eventful")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = 'Eventful requested for "' . $this->search_words . '" events.';
            return false;
        }

        $this->response = "Message not understood";
        return true;

    }

}

?>
