<?php
/**
 * Quiet.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Quiet {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function __construct(Thing $thing, $agent_input = null) {
        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "quiet";

        $this->agent_prefix = 'Agent "Quiet" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->save_to_stack = true;
        if ($this->agent_input != null) {
           $this->save_to_stack = false;
        }

        $this->node_list = array("off"=>array("on"=>array("off")));

        $this->current_time = $this->thing->json->time();

        if ($this->save_to_stack == true) {
        $this->variables_thing = new Variables($this->thing, "variables quiet " . $this->from);
        }

        $this->get(); // Updates $this->elapsed_time;

        $this->readSubject();


        $this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing_report['help'] = "This tells a Thing to be quiet. And not make messages for you." ;


        $this->thing_report['log'] = $this->thing->log;

    }


    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null) {

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        if ($this->save_to_stack == true) {
            $this->variables_thing->setVariable("state", $requested_state);
            $this->variables_thing->setVariable("refreshed_at", $this->current_time);
        }
        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }


    /**
     *
     */
    function get() {
        if ($this->save_to_stack == true) {
            $this->previous_state = $this->variables_thing->getVariable("state")  ;
            $this->refreshed_at = $this->variables_thing->getVariables("refreshed_at");
        }

        if (!isset($this->previous_state)) {$this->previous_state = "off";}


        if (!isset($this->requested_state)) {
            if (isset($this->state)) {
                $this->requested_state = $this->state;
            } else {
                $this->requested_state = $this->previous_state;

            }
        }

//        if (!isset($this->previous_state)) {$this->previous_state = "off";}

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->previous_state);
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;


        $this->state = $this->previous_state;
    }


    /**
     *
     * @return unknown
     */
    function read() {
        //$this->thing->log("read");

        //        $this->get();
        return $this->state;
    }



    /**
     *
     * @param unknown $choice (optional)
     * @return unknown
     */
    function selectChoice($choice = null) {

        if ($choice == null) {
            return $this->state;
        }


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".');

        $this->set($choice);

        return $this->state;
    }


    /**
     *
     */
    private function respond() {

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = $this->keyword;

        $choices = false;
        if ($this->save_to_stack == true) {  
            $choices = $this->variables_thing->thing->choice->makeLinks($this->state);
        }
        $this->thing_report['choices'] = $choices;

//        if (($this->state == "inside nest") or ($this->state == false)) {
//            $t = "NOT SET";
//        } else {
//            $t = $this->state;
//        }

//        $sms_message = "QUIET IS " . strtoupper($t);
        //        $sms_message .= " | Previous " . strtoupper($this->previous_state);
        //        $sms_message .= " | Now " . strtoupper($this->state);
        //        $sms_message .= " | Requested " . strtoupper($this->requested_state);
        //        $sms_message .= " | Current " . strtoupper($this->base_thing->choice->current_node);
        //        $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        //        $sms_message .= " | base nuuid " . strtoupper($this->variables_thing->thing->nuuid);

        //        $sms_message .= " | another nuuid " . substr($this->variables_thing->uuid,0,4);
//        $sms_message .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid, 0, 4);


//        $state_response = "TEXT QUIET HELP";


//        if ($this->state == "off") {
//            $state_response = "TEXT QUIET ON";
//        }
//        if ($this->state == "on") {
//            $state_response = "TEXT QUIET OFF";
//        }
//        $sms_message .= " | " . $state_response;

$this->makeSms();
$sms_message = $this->sms_message;
        $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
        $test_message .= '<br>Shift state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>Requested state: ' . $this->requested_state;
//        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

$this->thing_report['info'] = "Heard quiet.";
if ($this->agent_input == null) {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
}


        //$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

        $this->thing_report['help'] = 'This is Quiet.  You can tell a Thing to be Quiet. Or not.';



        return;


    }

    public function makeSms() {

        if (($this->state == "inside nest") or ($this->state == false)) {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $sms_message = "QUIET IS " . strtoupper($t);
if ($this->save_to_stack) {
        $sms_message .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid, 0, 4);
}
        if ($this->state == "off") {
            $state_response = "TEXT QUIET ON";
        }
        if ($this->state == "on") {
            $state_response = "TEXT QUIET OFF";
        }
        $sms_message .= " | " . $state_response;

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $this->response = null;

        $keywords = array('off', 'on');

if ($this->agent_input != null) {
    $input = strtolower($this->agent_input);
} else {
        $input = strtolower($this->subject);
}

        $haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        //  $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->read();
                return;
            }
            //return "Request not understood";
            // Drop through to piece scanner
        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                    case 'off':
                        $this->thing->log('switch off');
                        $this->selectChoice('off');
                        return;
                    case 'on':
                        $this->selectChoice('on');
                        return;
                    case 'next':

                    default:

                    }

                }
            }

        }


        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
        case 'on':
            $this->selectChoice('on');
            return;
        case 'off':
            $this->selectChoice('off');
            return;
        }

        $this->read();




        return "Message not understood";

        return false;


    }






    /**
     *
     * @return unknown
     */
    function kill() {
        // No messing about.
        return $this->thing->Forget();
    }


    /**
     *
     * @param unknown $input
     * @param unknown $discriminators (optional)
     * @return unknown
     */
    function discriminateInput($input, $discriminators = null) {


        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = array('on', 'off');
        }



        $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
        }



        $aliases = array();

        $aliases['on'] = array('red', 'on');
        $aliases['off'] = array('green', 'off');
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');



        $words = explode(" ", $input);

        $count = array();

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;

            $total_count = $total_count + 1;
        }
        // ...and the total count.



        foreach ($words as $word) {

            foreach ($discriminators as $discriminator) {

                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                }

                foreach ($aliases[$discriminator] as $alias) {

                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }

        }

        $this->thing->log('Agent "Flag" has a total count of ' . $total_count . '.');
        // Set total sum of all values to 1.

        $normalized = array();
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }


        // Is there good discrimination
        arsort($normalized);


        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key=>$value) {

            if ( isset($max) ) {$delta = $max-$value; break;}
            if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
        }




        if ($delta >= $minimum_discrimination) {
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }


}
