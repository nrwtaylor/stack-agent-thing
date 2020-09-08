<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Rhyme 
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


        $this->agent_prefix = 'Agent "Rhyme" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('rhyme','event','show','happening');

        $this->current_time = $this->thing->json->time();


        // $this->api_key = $this->thing->container['api']['rhyme'];

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "rhyme" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

//        if ($this->verbosity == false) {$this->verbosity = 2;}


		$this->thing->log('running on Thing '. $this->thing->nuuid . '.');
		$this->thing->log('received this Thing "'.  $this->subject . '".');

		$this->readSubject();

        $this->getRhyme();
        //if ($this->available_events_count > 10) {$this->getEventful('date');}


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





    function getRhyme($sort_order = null)
    {

        if ($sort_order == null) {$sort_order = "popularity";}


        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        $keywords = str_replace(" ", "%20%", $keywords);

        $data_source = "http://rhymebrain.com/talk?function=getRhymes&word=" . $keywords;

        $data = @file_get_contents($data_source);
        if ($data == false) {
            $this->response = "Could not ask Rhymebrain.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);

        $this->rhyme_words = $json_data;

        return true;

    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.rhymebrain.com"; 
        return $this->link;
    }

	private function respond()
    {
		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "rhyme";


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

        $this->thing_report['help'] = 'This provides rhymes from Rhymebrain API.';

        $this->thingreportRhyme();

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
        $html = "<b>RHYME</b>";
        $html .= "<p><b>Rhymebrain rhymes</b>";

        if (!isset($this->rhyme_words)) {$html .= "<br>No rhymes found on Rhymebrain.";} else {
            $html .= "<p>";
             foreach($this->rhyme_words as $index=>$rhyme) {
                $html .= $rhyme['word'];
                $html .= " /  ";
            }
        }
        $this->html_message = $html;
    }

    public function makeSms()
    {
        $sms = "RHYME ";
        $sms = strtoupper($this->search_words) . " | ";
        $i = 0;
        foreach($this->rhyme_words as $index=>$rhyme) {
            $sms .= $rhyme['word'];
            $i +=  1;
            if ($i >= 7) {break;}
            $sms .= " /  ";
        }

        $this->sms_message = $sms;
        return;
    }

    public function makeMessage()
    {
        $message = "Rhyme";

        $message = $this->response;

        $this->message = $message; 
        return;

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


    private function thingreportRhyme()
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

            if ($input == 'rhyme') {
                $this->search_words = "limerick";
                //$this->search_words = null;
                $this->response = "Asked Rhyhmebrain.com about rhymes.";
                return;
            }

        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "rhyme is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("rhyme is")); 
        } elseif (($pos = strpos(strtolower($input), "rhyme")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("rhyme")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response = "Asked Rhymebrain about " . $this->search_words . " rhyming words.";
            return false;
        }

        $this->response = "Message not understood.";
		return true;
	}
}

?>
