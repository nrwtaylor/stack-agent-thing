<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Contextualweb
{

    // This gets web search from Contextualweb.com.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;
        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Contextual Web" ';
        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('contextual','web','related');

        $this->current_time = $this->thing->time();

        $this->api_key = $this->thing->container['api']['mashape'];

        $this->variables_agent = new Variables($this->thing, "variables " . "contextualweb" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi();

		$this->respond();

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

    function getApi($type = null)
    {

        if ($type == null) {$type = null;}

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: application/json\r\n" .
              "X-Mashape-Key: " . $this->api_key . "\r\n" .  // check function.stream-context-cr$
              "" // i.e. An iPad 
            )
        );


        $context = stream_context_create($options);

        $keywords = urlencode($this->search_words);

        $data_source = "https://contextualwebsearch-websearch-v1.p.mashape.com/api/Search/WebSearchAPI?q=" . $keywords . "&count=3&autocorrect=true";

        $data = file_get_contents($data_source, false, $context);

        if ($data == false) {

            $this->response = "Could not ask Contextual Web.";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE); 

        $related = $json_data['relatedSearch'];

        $url = $json_data['value'][0]['url'];
        $definition = strip_tags($json_data['value'][0]['description']);

/*
$count = 0;
foreach ($definitions as $id=>$definition) {
    if (!isset($definition['definitions'][0])) {continue;}
    $this->definitions[] = $definition['definitions'][0];
    //var_dump($definition['definitions'][0]);
    $count += 1;
}
*/
//exit();

        $this->links[0] = $url;
        $this->definitions[0] = $definition;
        $this->definitions_count = 1;

        return false;

    }


    function eventsEventful($eventful_events)
    {
        if (!isset($this->events)) {$this->events = array();}
        if($eventful_events == null) {$this->definitions_count = 0;return;}

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

        $this->definitions_count = count($this->events);



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
        $thing->Create("eventful@stackr.ca","events", "s/ event eventful " . $eventful_id);

        // make sure the right fields are directly given

        new Event($thing, "event is ". $eventful_title);
        new Runat($thing, "runat is ". $run_at);
        new Place($thing, "place is ". $venue_name);
        new Link($thing, "link is " . $link);

    }

	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "eventful";

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

        $this->thing_report['help'] = 'This triggers provides currency prices using the 1forge API.';

        $this->thingreportContextualweb();

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


        $run_time = new Runtime($this->thing, $event['runtime']);

        if ($event['runtime'] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " "  . $event['place'];

        return $event_string;
    }

    public function makeWeb()
    {
        $html = "<b>CONTEXTUAL SEARCH</b>";
        $html .= "<p><b>Contextual Search Definitions</b>";

        if (!isset($this->events)) {$html .= "<br>No definitions found on Contextual Search.";} else {

            foreach ($this->events as $id=>$event) {

                $event_html = $this->eventString($event);

                //        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';
                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                //        $web .= $this->html_image;
                $html_link .= "eventful";
                $html_link .= "</a>";

                $html .= "<br>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = strtoupper($this->search_words);
        switch ($this->definitions_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .= " | " .$this->definitions[0] . " " . $this->links[0];
                break;
            default:
                foreach($this->definitions as $definition) {
                    $sms .= " / " . $definition;
            }
        }

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        $message = "Contextual Web";

        switch ($this->definitions_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' .$this->definitions[0] . '"';
                break;
            default:
                foreach($this->definitions as $definition) {
                    $message .= " / " . $definition;
                }

        }

        $this->message = $message;

    }

    private function thingreportContextualweb()
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

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'contextualweb') {
                //$this->search_words = null;
                $this->response = "Asked Contextual Web about nothing.";
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
        if (($pos = strpos(strtolower($input), "contextualweb is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("contextualweb is")); 
        } elseif (($pos = strpos(strtolower($input), "contextualweb")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("contextualweb")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = 'Asked about "' . $this->search_words . '".';
            return false;
        }

        $this->response = "Message not understood";
		return true;
	}
}

?>
