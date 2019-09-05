<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Brownpapertickets
{
    // This gets events from the Brownpapertickets API. Only Vancouver at the moment.

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

        $this->agent_prefix = 'Agent "Brown Paper Tickets" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('brownpapertickets','brown', 'paper', 'tickets', 'event','show','happening');

        $this->current_time = $this->thing->json->time();

        $this->developer_id = $this->thing->container['api']['brownpapertickets'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "brown_paper_tickets" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi();

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
        if (isset($this->events)) {return $this->events;}

        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        $c = new City($this->thing,"city");
        $city = $c->city_name; 

        if (strtolower($city) != "vancouver") {
            $this->response = "Events not enabled for " . $city . ".";
            $this->available_events_count = 0;
            $this->events = true;
            $this->events_count = 0;
            $this->thing->log( 'did not get any events.');

            return true;

        }
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = str_replace(" ", "%20%", $keywords);

        // Custom feed built in developer part of Brown Paper Tickets.
        $data = file_get_contents("https://www.brownpapertickets.com/eventfeed/627");

        if ($data == false) {
            $this->response = "Could not ask Brown Paper Tickets.";
            $this->available_events_count = 0;
            $this->events = true;
            $this->events_count = 0;
            $this->thing->log( 'did not get any events.');

            return true;
            // Invalid query of some sort.
        }

        $data_xml = simplexml_load_string($data);

        // devstack
        // https://stackoverflow.com/questions/6167279/converting-a-simplexml-object-to-an-array
        $events = json_decode(json_encode($data_xml), TRUE);

        $this->eventsBrownpapertickets($events);



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

    function eventsBrownpapertickets($events)
    {
        if (!isset($this->events)) {$this->events = array();}
        if($events == null) {$this->events_count = 0;return;}

        // devstack sort

        foreach($events['event'] as $not_used=>$event) {

            $city = "vancouver";
            if (strtolower($event['city']) != $city) {continue;}

            $id = $event['id'];

            $event_name = $event['event_name'];

            $description = $event['description'];
            // devstack extract dates from description
            // resolve multi-day events

            $run_at = $event['start_date']; // local event time
            $end_at = $event['end_date']; // local event time

            $runtime = strtotime($end_at) - strtotime($run_at);
            if ($runtime <= 0) {$runtime = "X";}

            $venue_name = $event['venue_name'];

            $venue_address = $event['venue_address'];

            if (is_array($event['link'])) {
                $link = null;
            } else {
                $link = $event['link'];
            }

            $event_array = array("event"=>$event_name, "runat"=>$run_at, "runtime"=>$runtime, "place"=>$venue_name, "link"=>$link, "datagram"=>$event);

            $pieces = $this->array_flatten($event_array, " ");

            // Make a haystack and needles to find.
            $haystack = implode(" " , $pieces);
            $needles = explode(" ",$this->search_words);

            $search_result = ($this->match_all($needles, $haystack));

            if ($search_result != false) {
                $this->events[$id] = $event_array;
            }
        }

        $this->events_count = count($this->events);
    }

    //http://activelab.io/code-snippets/check-if-a-string-contains-specific-words-in-php
    function match_all($needles, $haystack)
    {
        if(empty($needles)){
            return false;
        }

        foreach($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.brownpapertickets.com"; 
        return $this->link;
    }

    public function makeEvent($event)
    {
        throw new Exception('devstack.');

        // Need to check whether the events exists...
        // This can be post response.

        // devstack this will be an Event function
        // Just needs to pass the source to Event.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("brownpapertickets@stackr.ca","events", "s/ event brownpapertickets " . $eventful_id);

        // make sure the right fields are directly given

        new Event($thing, "event is ". $event['event']);
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
		$from = "brownpapertickets";

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->flag = "green";

        $this->makeSms();
        $this->makeMessage();

        $this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This provides events using the Brown Paper Tickets API.';

        $this->thingreportBrownpapertickets();

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

        if ($event['runtime'] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " "  . $event['place'];

        return $event_string;
    }

    public function makeWeb()
    {
        if (!isset($this->search_words)) {$s = "";} else {$s = $this->search_words;}

        $html = "<b>BROWN PAPER TICKETS " . $s . "</b>";
        $html .= "<p><b>Brown Paper Tickets Events</b>";

        if ((!isset($this->events)) or ($this->events === true)) {
            $html .= "<br>No events found on Brown Paper Tickets.";
        } else {

        foreach ($this->events as $id=>$event) {

            $event_html = $this->eventString($event);

            // Make a link to the Brown Paper Tickets page
            $link = "https://www.brownpapertickets.com/event/" . $id;
            $html_link = '<a href="' . $link . '">';
            $html_link .= "brown paper tickets";
            $html_link .= "</a>";

            $html_link_brownpapertickets = $html_link;

            // Get event link. Normally an artist/performer link.
            $link = $event['link'];

            if ($link != null) {

                $scheme = parse_url($link, PHP_URL_SCHEME);
                if (empty($scheme)) {
                    $link = 'http://' . ltrim($link, '/');
                }

                $html_link_event = '<a href="' . $link . '">';
                $html_link_event .= "event link";
                $html_link_event .= "</a>";

            } else {
                $html_link_event = "";
            }

            $html .= "<br>" . $event_html . " " . $html_link_event . " " . $html_link_brownpapertickets;;

            }
        }
        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "BROWN PAPER TICKETS";

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
        $message = "Brown Paper Tickets";

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


    private function thingreportBrownpapertickets()
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

            if ($input == 'brownpapertickets') {
                //$this->search_words = null;
                $this->response = "Asked Brown Paper Tickets about events.";
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
        if (($pos = strpos(strtolower($input), "brownpapertickets is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("brownpapertickets is")); 
        } elseif (($pos = strpos(strtolower($input), "brownpapertickets")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("brownpapertickets")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = "Asked Brown Paper Tickets about " . $this->search_words . " events";
            return false;
        }

        $this->response = "Message not understood";
		return true;

	}

}
?>
