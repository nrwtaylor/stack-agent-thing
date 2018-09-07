<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Random 
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

        $this->agent_name = "random";
        $this->agent_prefix = 'Agent "Random" ';

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

        $this->random = new Variables($this->thing, "variables random " . $this->from);

        //$this->subject = "Let's meet at 10:00";

        // Read the subject to determine intent.
		$this->readSubject();

        $this->doRandom();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();
        if ($this->agent_input == null) {
		    $this->Respond();
        }

        $this->set();

        $this->thing->log(' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;

		return;
    }

    function makeRandom($input = null)
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

        //echo $t->format("Y-m-d H:i:s");
        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $this->clock_time = $this->hour . $this->minute;

        //if ($input == null) {$this->random = $train_time;}
        return $this->clock_time;
    }


    function set()
    {
        //$this->head_code = "0Z15";
        //$headcode = new Variables($this->thing, "variables headcode " . $this->from);

        $this->random->setVariable("refreshed_at", $this->current_time);
        $this->random->setVariable("number", $this->number);

        $this->thing->log( $this->agent_prefix .' saved '  . $this->number . ".", "DEBUG" );

        return;
    }

/*
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
*/

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

        $this->number = $this->random->getVariable("number");

        return;
    }

    function extractRandom($input = null) 
    {
        // Get a random word from the string?

        if (is_numeric($input)) {
            // See if we received a unix timestamp number
        }

        $this->number = "Z"; // Number is available.

        return $this->number;
    }

    function read()
    {
//        $this->thing->log("read");
        return;
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
       // $min = 0;
       // $max = 10;

        // Built-in Mersenne Twist
        // Not cryyptographically secure
        $this->number = random_int($min, $max); 

    }

    public function randomMtrand($min = null, $max = null)
    {
        $min = 0;
        $max = 10;
        // Built-in Mersenne Twist
        // Not cryyptographically secure
        $this->number = mt_rand($min, $max); 

    }

    public function randomApi($uuid = null)
    {
    }

    public function doRandom()
    {


        //$this->randomMtrand();
        $this->randomRandomint();

    }


    private function makeWeb() {

        if (!isset($this->response)) {$this->response = "meep";}

        $m = '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

        //$m .= "CLOCKTIME<br>";
        $m .= "number " . $this->number . "<br>";

        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $m .= $this->response;

        $this->web_message = $m;
        $this->thing_report['web'] = $m;
    }


    function test()
    {

        // How random?

    }


    private function makeSMS()
    {
        $sms_message = "RANDOM";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | uuid " . $this->thing->uuid . " number " . $this->number;

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
		$from = "random";


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

        $this->thing_report['help'] = 'This is a random.  Extracting clock times from strings.';

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
        //if ($this->agent_input == "test") {$this->test(); return;}

        //$this->num_hits = 0;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.

//$assume_time = date('Y-m-d H:i:s', $this->agent_input);
//$assume_string = date('Y-m-d H:i:s', str_to_time($this->agent_input));

//echo $this->agent_input ." > " . $assume_time . " " . $assume_string . "\n";

            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $prior_uuid = null;

        // Is there a random number in the provided datagram
        $this->extractRandom($input);
        if ($this->agent_input == "extract") {$this->response = "Extracted a random.";return;}

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
                        case 'now':
                            $this->thing->log("read subject nextheadcode");
                            $t = $this->thing->time();
                            $this->extractRandom($t);
                            $this->response = "Got server time.";

                            return;

                    }
                }
            }
        }

        if (($this->minute == "X") and ($this->hour == "X")) {
            $this->get();
            $this->response = "Last random retrieved.";
        }

        return "Message not understood";

	}
}

?>

