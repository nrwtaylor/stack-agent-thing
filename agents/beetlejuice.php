<?php

error_reporting(E_ALL);ini_set('display_errors', 1);



require_once('/var/www/html/stackr.ca/lib/fpdf.php');
require_once('/var/www/html/stackr.ca/lib/fpdi.php');

require_once '/var/www/html/stackr.ca/agents/message.php';
require_once '/var/www/html/stackr.ca/agents/tally.php';

//include_once('/var/www/html/stackr.ca/src/pdf.php'); 


class Beetlejuice{

    // So Tally just increments a variable and keeps going past 0.
    // limit:5 => 1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2 ...
    // And that is what this does.

    // If an Agent gives it a command, it will set up the 
    // parameters of the Tally, which by default are:
    //   tally /  5   / mordok  /  tally@stackr.ca
    
    //   tally <tally_limit> <agent> <identity> ie
    // a tally of 5 for mordok for tally@stackr.ca

    // Without an agent instruction, tally
    // return the calling identities self-tally.

    //   tally /  5   / thing  /   $this->from

	function __construct(Thing $thing, $agent_command = null) 
    {
//$agent_command = "test";
       // $this->start_time = microtime(true);

        // Setup Thing
        $this->thing = $thing;


        $this->start_time = $this->thing->elapsed_runtime();

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
        //    $agent_command = null;
            $this->agent_command = $this->subject;
            $this->nom_input = $this->subject;

        } else {

            //$agent_command = null;
            $this->agent_command = $agent_command;
            $this->nom_input = $agent_command;


        }

        $this->current_time = $this->thing->json->time();


        $this->readInput();


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

     	$this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
    	$this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start");

		$this->thing->log( '<pre> ' .$this->agent_prefix . ' running on Thing ' .  $this->thing->nuuid .  ' </pre>' );

		$this->readText();

//        $this->thing->log( 'Flag Potentially Nominal - Agent "Tally" processed "' . $this->nom_input . '".' );


        $this->variables_agent = new Variables($this->thing, "variables " . "beetlejuice" . " " . $this->from);

//        $this->addTally();
        $this->get();

        $this->getDistance();

        if ($this->agent_command == null) {$this->Respond();}

        $this->set();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'Agent "Beetlejuice" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;
		return;
	}


    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.

        // This makes sure that
        //if (!isset($this->variables_agent)) {
        //    $this->variables_agent = $this->thing;
        //}

        if (!isset($this->requested_state)) {
            $this->requested_state = $this->state;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }



        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("distance_1", $this->distance_1);
        $this->variables_agent->setVariable("distance_2", $this->distance_2);
        $this->variables_agent->setVariable("distance_3", $this->distance_3);
        $this->variables_agent->setVariable("random_string", $this->random_string);


        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        //$this->thing->choice->save($this->agent, $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }



    function get()
    {
        $this->previous_state = $this->variables_agent->getVariable("state")  ;


        $this->distance_1 = $this->variables_agent->getVariable("distance_1")  ;
        $this->distance_2 = $this->variables_agent->getVariable("distance_2")  ;
        $this->distance_3 = $this->variables_agent->getVariable("distance_3")  ;
        $this->random_string = $this->variables_agent->getVariable("random_string")  ;

        $this->refreshed_at = $this->variables_agent->getVariables("refreshed_at");

//        $this->thing->choice->Create($this->agent, $this->node_list, $this->previous_state);

//        if (isset($this->requested_state)) {
//            $this->thing->choice->Choose($this->requested_state);
//            $this->state = $this->thing->choice->current_node;
//        } else {
//            $this->state = $this->previous_state;
//        }
        if (!isset($this->state)) {
            $this->state = $this->previous_state;
        }
        return;
    }



/**
 * function to generate random strings
 * @param       int     $length     number of characters in the generated string
 * @return      string  a new string is created with random characters of the desired length
 */
function makeRandomString($length = 200) {
    $randstr = null;
    srand((double) microtime(TRUE) * 1000000);
    //our array add all letters and numbers if you wish
    $chars = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
        '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 
        'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    for ($rand = 0; $rand <= $length; $rand++) {
        $random = rand(0, count($chars) - 1);
        $randstr .= $chars[$random];
    }
    return $randstr;
}

    function distance($string1 = null, $string2 = null)
    {
        $sum = 0;
        if (count($string1) < count($string2)) {$string_temp = $string2; $string2 = $string1; $string1= $string_temp;}

        $arr1 = str_split($string1);
        $arr2 = str_split($string2);
 
        $i =0;
        foreach($arr1 as $ch) {


//echo $ch . " " . ord($ch) . "<br>";
//echo $arr2[$i]. " " . ord($arr2[$i]) . "<br>";

            $d = abs(ord($ch) - ord($arr2[$i]));
            $sum += $d;
//echo "delta " .$d . "<br>";

            $i += 1;
        }

//exit();
        return $sum;
    }

    function getDistance()
    {
        if ((!isset($this->random_string)) or ($this->random_string == false)) {
            $this->random_string = $this->makeRandomString();
        }

        //echo $this->random_string;

        $distance = $this->distance($this->nom_input, $this->random_string);
        //echo $distance;

        $this->distance_3 = $this->distance_2;
        $this->distance_2 = $this->distance_1;
        $this->distance_1 = $distance;

        if ( (($this->distance_3 - $this->distance_2) == 0 ) and
             (($this->distance_2 - $this->distance_1) == 0 ) and
             (($this->distance_1 - $this->distance_3) == 0 )) {
            // Exact match
            $this->setFlag("red");

            // And reset the patten matcher
            $this->random_string = $this->makeRandomString();

        } else {
            // Reset the random string
 //           $this->random_string = $this->makeRandomString();
            $this->setFlag("green");
        }

        return $this->flag;
    
    }



	public function Respond() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 


        $this->thing->log( 'Agent "Beetlejuice" flag is ' . strtoupper($this->flag) . '.' );

        $this->sms_message = "BEETLEJUICE";

        if ((strtolower($this->subject) == "beetlejuice") and (strtolower($this->flag) == "red")) {        
		    $this->sms_message .= " = IT'S SHOWTIME";
        }
        //$this->sms_message .= " | was called";

        $this->sms_message .= " | flag " . strtoupper($this->flag);

//        $this->sms_message .= " | distance_1 " . $this->distance_1 ;
//        $this->sms_message .= " | distance_2 " . $this->distance_2 ;


//        $this->sms_message .= " | distance delta " . ($this->distance_2 - $this->distance_1);

        $this->sms_message .= " | text " . ($this->nom_input);
        


        if (isset($this->flag)) {
            $this->sms_message .= " | " . $this->flag;
        }
		$this->sms_message .= ' | TEXT ?';

		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['sms'] = $this->sms_message;


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);



		return $this->thing_report;
	}


    public function defaultCommand() 
    {
        $this->agent = "beetlejuice";
        $this->limit = 5;
        $this->name = "beetlejuice";
        $this->identity = $this->from;
        return;
    }


    public function readInstruction() 
    {
        return;
        if($this->nom_input == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
//        $this->limit = $pieces[1];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        if (!isset($pieces[3])) {
            $this->limit = 3;
        } else {        
            $this->limit = $pieces[3];
        }


        if (!isset($pieces[4])) {
            $this->index = 0;
        } else {        
            $this->index = $pieces[4];
        }


        $this->thing->log( 'Agent "Beetlejuice" read the instruction and got ' . $this->agent . ' ' . $this->limit ." ". $this->name . ' ' . $this->identity . "." );

        return;

    }


    function getFlag() 
    {
        if (!isset($this->flag)) {
            $this->get();
            $this->flag = $this->state;
        }

        //$this->flag_thing = new Flag($this->thing, 'flag');
        //$this->flag = $this->flag_thing->state; 

        return $this->flag;
    }

    function setFlag($colour) 
    {
        //$this->flag_thing = new Flag($this->thing, 'flag '.$colour);
        $this->flag = $colour;
        //$this->flag_thing->state; 

        return $this->flag;
    }




	public function readText() {

        // No need to read text.  Any identity input to Tally
        // increments the tally.
     
        return;
	}

    public function readInput() {
        $this->readInstruction();
        $this->readText();
        return;
    }


}

?>
