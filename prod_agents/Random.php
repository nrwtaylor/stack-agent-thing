<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Random extends Agent
{

    public $var = 'hello';
    function init()
    {
        $this->keywords = array('now','next', 'accept', 'clear', 'drop','add','new');
		$this->test= "Development code"; // Always iterative.
        $this->min = 0;
        $this->max = 10;
    }

    function run()
    {
        $this->random = new Variables($this->thing, "variables random " . $this->from);
        $this->doRandom();
    }

    function set()
    {
        $this->random->setVariable("refreshed_at", $this->current_time);
        $this->random->setVariable("number", $this->number);

        $this->thing->log( $this->agent_prefix .' saved '  . $this->number . ".", "DEBUG" );
    }

    function getRunat()
    {

        if (!isset($this->random)) {
            if (isset($random)) {
               $this->random = $random;
            } else {
                $this->random = "Meep";
            }
        }
        return $this->random;

    }

    function get($run_at = null)
    {
        $this->random = new Variables($this->thing, "variables random " . $this->from);
        $this->number = $this->random->getVariable("number");
    }

    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }

    public function randomUuid($uuid = null)
    {

    }

    public function randomRandomint($min = null, $max = null)
    {
        if (($max == null) and ($min != null))  {$max = $min;}
        if (($max == null) and ($min == null))  {$max = 10;$min = 1;}

        // https://www.php.net/manual/en/function.random-int.php
        $this->number = random_int($min, $max);
    }

    public function randomApi($uuid = null)
    {
    }

    public function doRandom()
    {
        $this->randomRandomint($this->min, $this->max);

        $this->response = "Created a random integer between " . $this->min  . " and " . $this->max . ".";

    }

    public function makeWeb() {

        if (!isset($this->response)) {$this->response = "No response provided.";}

        $web = "<b>Random Agent</b>";
        $web .= "<p>";



        $web .= "A random number is " . $this->number . "<br>";

        $web .= $this->response;

        $this->web_message = $web;
        $this->thing_report['web'] = $web;
    }


    function test()
    {
        // How random?
    }


    public function makeSMS()
    {
        $sms_message = "RANDOM";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | number " . $this->number;

        if (isset($this->response)) {$sms_message .= " | " . $this->response;}

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

	public function respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "random";


        $choices = false;
		$this->thing_report['choices'] = $choices;

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

        $this->thing_report['help'] = 'This is a random.  Extracting clock times from strings.';

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

        // Is there a random number in the provided datagram
        //$this->extractRandom($input);
        if ($this->agent_input == "extract") {$this->response = "Extracted a random.";return;}


        $number_agent = new Number($this->thing, "number");
        $number_agent->extractNumbers($input);

        if ((isset($number_agent->numbers)) and count($number_agent->numbers) == 2) {

            if ($number_agent->numbers[1] < $number_agent->numbers[0]) {
                $this->min = $number_agent->numbers[1];
                $this->max = $number_agent->numbers[0];
            } else {
                $this->min = $number_agent->numbers[0];
                $this->max = $number_agent->numbers[1];
            }
        }

        if ((isset($number_agent->numbers)) and count($number_agent->numbers) == 1) {
            $this->min = 0;
            $this->max = $number_agent->numbers[0];
        }



        $pieces = explode(" ", strtolower($input));


     if (count($pieces) == 1) {
            if ($input == 'random') {
                $this->get();
                $this->response = "Made random numbers.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {
                        case null:
                            return;
                    }
                }
            }
        }

        $this->get();

        return "Message not understood";

	}
}
