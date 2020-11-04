<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Clocktime 
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_name = "clocktime";
        $this->agent_prefix = 'Agent "Clocktime" ';

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

        $this->keywords = array('now','next', 'accept', 'clear', 'drop','add','new');

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.

        $this->current_time = $this->thing->json->time();

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

        $this->state = null; // to avoid error messages

        $this->clocktime = new Variables($this->thing, "variables clocktime " . $this->from);

        //$this->subject = "Let's meet at 10:00";

        // Read the subject to determine intent.
		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();

        if ($this->agent_input == null) {
		    $this->Respond();
        }

        $this->set();

        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;

		return;
    }

    function makeClocktime($input = null)
    {

        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }


        if (strtoupper($input) == "X") {
            $this->clock_time  = "X";
            return $this->clock_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $this->clock_time = $this->hour . $this->minute;

        //if ($input == null) {$this->clocktime = $train_time;}
        return $this->clock_time;
    }


    function test()
    {
        $test_corpus = file_get_contents($this->resource_path . "clocktime/test.txt");
        $test_corpus = explode("\n", $test_corpus);

        $this->response = "";
        foreach ($test_corpus as $key=>$line) {

            if ($line == "-") {break;}
            $this->extractClocktime($line);

            $line."<br>".
            "hour " . $this->hour . " minute " . $this->minute . "<br>".
            "<br>";
        }
    }

    function set()
    {
        //$this->head_code = "0Z15";
        //$headcode = new Variables($this->thing, "variables headcode " . $this->from);

        $this->clocktime->setVariable("refreshed_at", $this->current_time);
        $this->clocktime->setVariable("hour", $this->hour);
        $this->clocktime->setVariable("minute", $this->minute);

        $this->thing->log( $this->agent_prefix .' saved '  . $this->hour . " " . $this->minute . ".", "DEBUG" );

        return;
    }

    function getRunat()
    {

        if (!isset($this->clocktime)) {
            if (isset($clocktime)) {
               $this->clocktime = $clocktime;
            } else {
                $this->clocktime = "Meep";
            }
        }
        return $this->clocktime;

    }

    function get($run_at = null)
    {

        $this->hour = $this->clocktime->getVariable("hour");
        $this->minute = $this->clocktime->getVariable("minute");

        return;
    }

    function extractClocktime($input = null) 
    {

        if (is_numeric($input)) {
            // See if we received a unix timestamp number
            $input = date('Y-m-d H:i:s', $input);
        }

        $this->parsed_date = date_parse($input);

        $this->minute = $this->parsed_date['minute']; 
        $this->hour = $this->parsed_date['hour']; 

        if (($this->minute == false) and ($this->hour == false)) {

            // Start here
            $this->minute = "X";
            $this->hour = "X";

            // Test for non-recognized edge case
            if (preg_match("(o'clock|oclock)", $input) === 1) {

                $number_agent = new Number($this->thing, "number " . $input);
                if (count($number_agent->numbers) == 1) {
                    $this->hour = $number_agent->numbers[0];
                    if ($this->hour > 12) {$this->hour = "X";}
              }
            }

$pattern = '/\b([0-1]?[0-9]|2[0-3]):[0-5][0-9]\b/';

// TODO Recognize non-colon seperator

preg_match_all($pattern, $input, $m);
if (count($m[0]) == 1) {
    $t= explode(":",$m[0][0]);
    $this->minute = $t[1];
    $this->hour = $t[0];
}

            // Test for non-recognized edge case
            if (strpos($input, '0000') !== false) {
                $this->minute = 0;
                $this->hour = 0;
            }

            if (($this->hour == "X") and ($this->minute == "X")) {return null;}
        }

        return array($this->hour, $this->minute);
    }

    function read()
    {
//        $this->thing->log("read");
        return;
    }

    function makeTXT() {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;





    }

    public function makeWeb() {

        if (!isset($this->response)) {$this->response = "meep";}

        $m = '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

        //$m .= "CLOCKTIME<br>";
        $m .= "hour " . $this->hour . " minute " . $this->minute . "<br>";

        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $m .= $this->response;

        $this->web_message = $m;
        $this->thing_report['web'] = $m;
    }



    public function makeSMS()
    {
        $sms_message = "CLOCKTIME";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | hour " . $this->hour . " minute " . $this->minute;

        if (isset($this->response)) {$sms_message .= " | " . $this->response;}

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

	private function Respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "clocktime";


		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        $this->makeSMS();

	    $this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();
        $this->makeweb();

        $this->thing_report['help'] = 'This is a clocktime.  Extracting clock times from strings.';

		return;

	}

    function isData($variable)
    {
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
        if ($this->agent_input == "test") {$this->test(); return;}

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

        $prior_uuid = null;

        // Is there a clocktime in the provided datagram
        $this->extractClocktime($input);
        if ($this->agent_input == "extract") {$this->response = "Extracted a clocktime.";return;}

        $pieces = explode(" ", strtolower($input));


     if (count($pieces) == 1) {
            if ($input == 'clocktime') {
                $this->get();
                $this->response = "Last 'clocktime' retrieved.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {
                        case 'now':
                            $this->thing->log("read subject nextheadcode");
                            $t = $this->thing->time();
                            $this->extractClocktime($t);
                            $this->response = "Got server time.";

                            return;

                    }
                }
            }
        }

        if (($this->minute == "X") and ($this->hour == "X")) {
            $this->get();
            $this->response = "Last clocktime retrieved.";
        }
/*
        // Added in test 2018 Jul 26
        if (($this->minute == false) and ($this->hour == false)) {

            $t = $this->thing->time();
            $this->extractClocktime($t);
            $this->response = "Got server time.";
        }
*/
        return "Message not understood";

	}
}
