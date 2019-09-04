<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Google
{

    // This does Google Search via Google's API.

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


        $this->agent_prefix = 'Agent "Google" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('google','search','web');

        $this->current_time = $this->thing->json->time();

        $this->global_engine_id = $this->thing->container['api']['google']['custom_search']['global_engine_id'];
        $this->ca_engine_id = $this->thing->container['api']['google']['custom_search']['ca_engine_id'];
        $this->api_key = $this->thing->container['api']['google']['custom_search']['api_key'];

        $this->variables_agent = new Variables($this->thing, "variables " . "google" . " " . $this->from);

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
        // http://api.eventful.com/docs/events/search

        //count_only boolean
        //    If count_only is set, an abbreviated version of the output will be returned. Only total_items and search_time elements are included in the result. (optional) 

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

        if (!isset($this->search_words)) {
            $keywords = "google";
        } else {
            $keywords = urlencode($this->search_words);
        }

        $data_source = "https://www.googleapis.com/customsearch/v1?key=" . $this->api_key . "&cx=" . $this->ca_engine_id . "&q=" . $keywords;

        $data = @file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Google.";
            $this->definitions_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        $link = $json_data['items'][0]['link'];

        $definition = $json_data['items'][0]['snippet'];

/*
$count = 0;
foreach ($definitions as $id=>$definition) {
    if (!isset($definition['definitions'][0])) {continue;}
    $this->definitions[] = $definition['definitions'][0];
    //var_dump($definition['definitions'][0]);
    $count += 1;
}
*/

        $this->links[0] = $link;
        $this->definitions[0] = $definition;
        $this->definitions_count = 1;

        return false;

    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref; 
        return $this->link;
    }

	private function respond()
    {
		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "google";

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

        $this->thing_report['help'] = 'This provides web search via the Google API.';

        $this->thingreportGoogle();

		return;
	}

    public function makeWeb()
    {
        $html = "<b>GOOGLE</b>";
        $html .= "<p><b>Google Defintitions</b>";

        if (!isset($this->events)) {
            $html .= "<br>No definitions found on Google.";
        } else {

            foreach ($this->events as $id=>$event) {
                $event_html = $this->eventString($event);

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
        $sms = "GOOGLE";

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

        // Really need to refactor this double :/
        $this->sms_message = $sms;

    }

    public function makeMessage()
    {
        $message = "Google";

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

       // $message .= " | " . $this->response;

        $this->message = $message;

    }


    private function thingreportGoogle()
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

            if ($input == 'google') {
                //$this->search_words = null;
                $this->response = "Asked Google about nothing.";
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
        if (($pos = strpos(strtolower($input), "google is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("google is"));
        } elseif (($pos = strpos(strtolower($input), "google")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("google"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = 'Asked Google about "' . $this->search_words . '".';
            return false;
        }

        $this->response = "Message not understood";
		return true;

	}
}

?>
