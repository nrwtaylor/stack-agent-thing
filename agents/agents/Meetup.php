<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Meetup
{

    // This gets events from the Meetup API.

    // devstack Meetup v2 API was deprecated.
    // Rewrite this agent https://www.meetup.com/meetup_api/auth/#oauth2

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    //function init()
    {
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $agent_input;

        $this->keyword = "meetup";

        $this->thing = $thing;
        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->agent_prefix = 'Agent "Meetup" ';
        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('meetup','meet-up','event','show','happening');

        $this->current_time = $this->thing->time();

        $this->api_key = $this->thing->container['api']['meetup'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "meetup" . " " . $this->from);

        // Loads in variables.
        $this->get();

		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getMeetup();

		$this->respond();

		$this->thing->log( 'completed.');

        $this->thing->log(' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

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

    function getMeetup($sort_order = null)
    {
        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        $c = new City($this->thing,"city");
        $city = $c->city_name; 

        // "America/Vancouver" apparently
        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        //$keywords = str_replace(" ", "%20%", $keywords);

        $keywords = urlencode($keywords);

        // Let's use meetup popularity...
//        $data_source = "https://api.meetup.com/2/open_events.xml?format=json&and_text=true&text=" . $keywords . "&time=,1w&key=". $this->api_key;
        $data_source = "https://api.meetup.com/find/upcoming_events?format=json&and_text=true&text=" . $keywords . "&time=,1w&key=". $this->api_key;
$data_source = "https://api.meetup.com/find/upcoming_events?&sign=true&photo-host=public&page=20";

        $time = "&time=,1w";
        $time = ""; // turn time paramaters off

        $format = "format=json"; // json or xml
        // $data_source = "https://api.meetup.com/2/open_events.xml?format=json&country=ca&city=vancouver&and_text=true&text=" . $keywords . "&time=,1w&key=". $this->api_key;
        // $data_source = "https://api.meetup.com/2/open_events.xml?format=json&country=ca&city=vancouver&and_text=true&text=" . $keywords . $time . "&key=". $this->api_key;

        $data_source = "https://api.meetup.com/2/open_events.xml?" . $format . "&country=ca&city=vancouver&and_text=true&text=" . $keywords . $time . "&key=". $this->api_key;

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);





        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
                    "app_id: " . $this->application_id . "\r\n" .  // check function.stream-context-create on php.net
                    "app_key: " . $this->application_key . "\r\n" . 
                    "" // i.e. An iPad 
            )
        );

        $context = stream_context_create($options);

//        $data_source = "https://od-api.oxforddictionaries.com:443/api/v1/entries/en/". $keywords;

//get /entries/{source_lang}/{word_id}/synonyms
//get /entries/{source_lang}/{word_id}/synonyms

        $data = @file_get_contents($data_source, false, $context);

        if ($data == false) {
            $this->response = "Could not ask Oxford Dictionaries.";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);









$data_source = "https://api.meetup.com/find/upcoming_events?&sign=true&photo-host=public&page=20";


        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Meetup.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);

        $total_items = $json_data['meta']['total_count'];

        $this->thing->log('got ' . $total_items . " Event things.");

        $this->available_events_count = $total_items;

        $meetup_events = $json_data['results'];

        $this->eventsMeetup($meetup_events); // Custom function to match Meetup API variables to Events.
        return false;
    }

    function eventsMeetup($meetup_events)
    {
        if (!isset($this->events)) {$this->events = array();}
        if($meetup_events == null) {$this->events_count = 0;return;}

        foreach($meetup_events as $id=>$event) {

            // Privacy check. If there is a flag in the API return. Respect it. Ignore the event.
            $visibility = $event['visibility'];
            if ($visibility != "public") {echo "privacy";continue;}

            // Region check.
            //if (!isset($event['venue']['state'])) {var_dump($event);}
            //var_dump($event['venue']['state']);
            //$region_abbr = $event['venue']['state'];
            if ((isset($event['venue']['state'])) and (strtolower($event['venue']['state']) != "bc")) {
                //ignore
                continue;
            } // Restrict to BC events in dev/test/prod

            // Get the longest text string (promoter provided) available.
            if (!isset($event['description'])) {$description = "X";} else {$description = $event['description'];}


            if (!isset($event['venue'])) {
                $venue_name = null;
                $venue_city = null;
                $venue_address = null;
            } else {
                $venue_name = $event['venue']['name'];
                $venue_city = $event['venue']['city'];
                $venue_address = null;
                if (isset($event['venue']['address_1'])) {$venue_address = $event['venue']['address_1'];}
            }

            // What is this Thing called?
            $event_name = $event['name'];

            // And what unique ID does the service give it. 
            // This will help us reduce CPU and Network load later. 
            $meetup_id = $event['id'];

            //    $run_at = $event['time']; // local event time?  check.
            $run_at = date('c',$event['time']/1000); // Apply the necessary conversions.

            if (!isset($event['duration'])) {$runtime = "X";} else {$runtime = $event['duration']/1000;}

            $link = $event['event_url'];

            $this->events[$meetup_id] = array("event"=>$event_name, "runat"=>$run_at, "runtime"=>$runtime, "place"=>$venue_name, "link"=>$link);

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
        // Need to check whether the events exists...
        // This can be post response.   

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("meetup@stackr.ca","events", "s/ event meetup " . $eventful_id);

        // make sure the right fields are directly given
        new Event($thing, "event is ". $event['name']);
        new Runat($thing, "runat is ". $event['runat']);
        new Place($thing, "place is ". $event['place']);
        new Link($thing, "link is " . $event['link']);
    }

	private function respond()
    {
		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "meetup";

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

        $this->thingreportMeetup();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        if (isset($this->event)) {
            foreach($this->events as $id=>$event) {
                var_dump($event);
            }
        }

        $this->thing_report['help'] = 'This provides events using the Application Programming Interface (API) provided Meetup.com.';

		return;
	}

    public function eventString($event)
    {
        $event_date = date_parse($event['runat']);

        $month_number = $event_date['month'];
        if ($month_number == "X") {$month_name = "XXX";} else {
        $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March
        }
        $simple_date_text = $month_name . " " . $event_date['day'];

        $event_string = ""  . $simple_date_text;
        $event_string .= " "  . $event['event'];

        $runat = new Runat($this->thing, "extract " . $event['runat']);

        $event_string .= " "  . $runat->day;
        $event_string .= " "  . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":"  . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);


        $run_time = new Runtime($this->thing, "extract " . $event['runtime']);

        if ($event['runtime'] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " "  . $event['place'];


        return $event_string;

    }

    public function makeWeb()
    {
        $html = "<b>MEETUP</b>";
        $html .= "<p><b>Meetup Events</b>";

        if (!isset($this->events)) {
            $html .= "<br>No events found on Meetup.";
        } else {
            foreach ($this->events as $id=>$event) {
                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                $html_link .= "meetup";
                $html_link .= "</a>";

                $html .= "<p>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "MEETUP";

        switch ($this->events_count) {
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

        $this->sms_message = $sms;
    }

    public function makeMessage()
    {
        $message = "Meetup";

        switch ($this->events_count) {
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

    private function thingreportMeetup()
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

		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;
        //$prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'meetup') {
                //$this->search_words = null;
                $this->response = "Asked Meetup about events.";
                return;
            }

        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "meetup is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("meetup is")); 
        } elseif (($pos = strpos(strtolower($input), "meetup")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("meetup")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = "Asked Meetup about " . $this->search_words . " events";
            return false;
        }

        $this->response = "Message not understood";
		return true;

	}

}
