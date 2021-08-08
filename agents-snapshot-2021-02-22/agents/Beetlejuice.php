<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

// devstack
// not operational

class Beetlejuice extends Agent {

    // Looks for a repeat.  Three times.

    function init()
    {

        $this->start_time = $this->thing->elapsed_runtime();

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        $this->current_time = $this->thing->json->time();


        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.


        $this->node_list = array("start");

	}


    function set()
    {

        if (!isset($this->requested_state)) {
            $this->requested_state = $this->state;
        }

//        if ($requested_state == null) {
            $requested_state = $this->requested_state;
//        }

        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("distance_1", $this->distance_1);
        $this->variables_agent->setVariable("distance_2", $this->distance_2);
        $this->variables_agent->setVariable("distance_3", $this->distance_3);
        $this->variables_agent->setVariable("random_string", $this->random_string);


        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        //$this->thing->choice->save($this->agent, $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

    }



    function get()
    {
        $this->variables_agent = new Variables($this->thing, "variables " . "beetlejuice" . " " . $this->from);

        $this->previous_state = $this->variables_agent->getVariable("state")  ;


        $this->distance_1 = $this->variables_agent->getVariable("distance_1")  ;
        $this->distance_2 = $this->variables_agent->getVariable("distance_2")  ;
        $this->distance_3 = $this->variables_agent->getVariable("distance_3")  ;
        $this->random_string = $this->variables_agent->getVariable("random_string")  ;

        $this->refreshed_at = $this->variables_agent->getVariables("refreshed_at");

        if (!isset($this->state)) {
            $this->state = $this->previous_state;
        }

    }

    public function run() {

        $this->distanceBeetlejuice();

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
        if (mb_strlen($string1) < mb_strlen($string2)) {$string_temp = $string2; $string2 = $string1; $string1= $string_temp;}

        $arr1 = str_split($string1);
        $arr2 = str_split($string2);
        $i =0;
        foreach($arr1 as $ch) {

            if (!isset($arr2[$i])) {break;}

            $d = abs(ord($ch) - ord($arr2[$i]));
            $sum += $d;

            $i += 1;
        }

        return $sum;
    }

    function distanceBeetlejuice()
    {
        if ((!isset($this->random_string)) or ($this->random_string == false)) {
            $this->random_string = $this->makeRandomString();
        }

        $distance = $this->distance($this->nom_input, $this->random_string);

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

    public function makeSMS() {

        $this->sms_message = "BEETLEJUICE";

        if ((strtolower($this->subject) == "beetlejuice") and (strtolower($this->flag) == "red")) {        
                    $this->sms_message .= " = IT'S SHOWTIME";
        }

        $this->sms_message .= " | flag " . strtoupper($this->flag);
        $this->sms_message .= " | text " . ($this->nom_input);

        if (isset($this->flag)) {
            $this->sms_message .= " | " . $this->flag;
        }
                $this->sms_message .= ' | TEXT ?';

                $this->thing_report['sms'] = $this->sms_message;


    }

    public function respondResponse() {

        // Develop the various messages for each channel.

        // Thing actions
        $this->thing->flagGreen(); 

        // While we work on this
	$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);
    }


    public function defaultCommand() 
    {
        $this->agent = "beetlejuice";
        $this->limit = 5;
        $this->name = "beetlejuice";
        $this->identity = $this->from;
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

    }


    function getFlag() 
    {
        if (!isset($this->flag)) {
            $this->get();
            $this->flag = $this->state;
        }

        return $this->flag;
    }

    function setFlag($colour) 
    {
        $this->flag = $colour;
        return $this->flag;
    }

    public function readBeetlejuice() {
    }


    public function read($variables = null) {

        $agent_command = $this->agent_input;
        if ($agent_command == null) {
        //    $agent_command = null;
            $this->agent_command = $this->subject;
            $this->nom_input = $this->subject;
        } else {

            //$agent_command = null;
            $this->agent_command = $agent_command;
            $this->nom_input = $agent_command;
        }

        $this->readInput();
        // No need to read text.  Any identity input to Tally
        // increments the tally.
    }

    public function readInput() {
        $this->readInstruction();
        $this->readBeetlejuice();
    }

}
