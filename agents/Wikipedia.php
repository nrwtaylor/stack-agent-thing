<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wikipedia 
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $agent_input;

        $this->keyword = "know";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Wikipedia" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('wikipedia','definition');

        $this->current_time = $this->thing->time();

        $this->application_id = null;
        $this->application_key = null;

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "wikipedia" . " " . $this->from);

        // Loads in variables.
        $this->get();

		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getApi();

		$this->respond();

        $this->thing->log('ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');
		$this->thing->log( 'completed.');

        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;

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





    function getApi($sort_order = null)
    {

        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = urlencode($keywords);

/*
$options = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: application/json\r\n" .
              "" // i.e. An iPad 
  )
);

$context = stream_context_create($options);
*/

      //  $data_source = "https://en.wikipedia.org/api/rest_v1/page/summary/bridge";

        //$titles = "&titles=New_York_Yankees";
        $titles = "&titles=". $keywords;

        $format = "&format=json";

        $rvprop = "&rvprop=timestamp|user|comment|content";
        $rvprop = "";

        $prop ="&prop=revisions";
        $prop = "&prop=extracts";

        //if we just want the intro, we can use exintro. Otherwise it shows all sections
        $exintro = "&exintro=1";
        $list = "&list=search";

        //$srsearch = "&srsearch=皮皮果";
        $srsearch = "&srsearch=". $keywords;

        // Experiments
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $prop . $exintro . $format . $prop . $titles . $rvprop;
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=皮皮果&utf8=&format=json";
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $srsearch . $prop . $exintro . $format . $rvprop;
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $srsearch . $prop . $exintro . $format . $rvprop;

        // Gets a list of matches
        $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $list . $srsearch . "&utf8=&format=json";

        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Wikipedia.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        if (!isset($json_data['query']['search'][0]['snippet'])) {

            $this->text = "Wikipedia did not find anything.";
            return true;

        }

        $snippet = strip_tags($json_data['query']['search'][0]['snippet']);
        $this->text = html_entity_decode($snippet);
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
		$from = "wikipedia";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->makeSms();
        $this->makeMessage();

        $this->makeWeb();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportWikipedia();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This asks Wikipedia about the words provided.';

		return;
	}

    public function makeWeb()
    {
        $html = "<b>WIKIPEDIA</b>";
        $html .= "<p><b>Wikipedia Text</b>";

        if (!isset($this->text)) {
            $html .= "<br>Nothing found on Wikipedia.";
        } else {

            $html .= "<br>" . $this->text;
        }
        $this->html_message = $html;
    }

    function truncate($string,$length=100,$append="[...]")
    {
        $string = trim($string);

        if(strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }
        return $string;
    }

    public function makeSms()
    {
        $sms = "WIKIPEDIA | ";

        if ($this->text == "") {
            $text = "Nothing found.";
        } else {
            $text = $this->truncate($this->text,100);
        }

        $sms .= $text;
        // $sms .= $this->truncate($this->text,130);
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
    }

    public function makeMessage()
    {
        if ($this->text == "") {
            $text = "Nothing found.";
        } else {
            $text = $this->truncate($this->text,100);
        }

        if (substr_count($text, '"') > 0) {
            $quotation_mark = "'";
        } else {
            $quotation_mark = '"';
        }

        $message = 'Wikipedia said, ' . $quotation_mark;

        $message .= $text;
        $message .= $quotation_mark;

        $this->message = $message;
    }

    private function thingreportWikipedia()
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
        // Extract uuids into

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

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'wikipedia') {
                //$this->search_words = null;
                $this->response = "Asked Wikipedia about everything.";
                return;
            }

        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "wikipedia is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("wikipedia is")); 
        } elseif (($pos = strpos(strtolower($input), "wikipedia")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("wikipedia")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = "Asked Wikipedia about " . $this->search_words . ".";
            return false;
        }

        $this->response = "Message not understood";
		return true;
	}

}

?>
