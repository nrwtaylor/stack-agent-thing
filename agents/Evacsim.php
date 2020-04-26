<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';
//require_once '/var/www/html/stackr.ca/agents/message.php';
//require_once '/var/www/html/stackr.ca/agents/variables.php';

//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Evacsim 
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        $this->start_time = microtime(true);



        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "evacsim";

        $this->agent_prefix = 'Agent "Evacsim" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->start_time = $this->thing->elapsed_runtime();

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->node_list = array("off"=>array("on"=>array("off","unit knock"=>array("blue","pink","red","yellow","orange"),"report clear")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

        $this->variables_thing = new Variables($this->thing, "variables evacsim " . $this->from);

//echo $this->from;

//echo $this->thing->variables_thing->variable;
//echo "<pre>";
//print_r($this->variables_thing->getVariable("state"));
//echo "</pre>";
//exit();
// //$agent_command = $this->agent . " "  . $this->variable_set_name . " " . $this->from;


        $this->get(); // Updates $this->elapsed_time;

        // And so at this point we have a timer model.

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()



		$this->thing->log('Agent "Evacsim" running on Thing ' . $this->thing->nuuid . ".");
		$this->thing->log('Agent "Evacsim" received this Thing, "' . $this->subject .  '".') ;

		$this->readSubject();

$this->thing->log( $this->agent_prefix .'. Timestamp  ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

		$this->respond();

		//$this->thing->log( '<pre> Agent "Mordok" completed and is showing a ' . $this->state . ' flag.</pre>');

        //$this->end_time = microtime(true);
        //$this->actual_run_time = $this->end_time - $this->start_time;
        //$milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;


		return;

		}


    function set($requested_state = null)
    {
 
        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

//        $this->thing->json->setField("variables");
//        $this->thing->json->writeVariable( array($this->keyword, "state"), $requested_state );
//        $this->thing->json->writeVariable( array($this->keyword, "refreshed_at"), $this->current_time );

        $this->variables_thing->setVariable("state", $requested_state);
        $this->variables_thing->setVariable("refreshed_at", $this->current_time);

      

        $this->thing->choice->Choose($requested_state);


        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;


//$this->thing->log("Result of choice->load() ". $this->thing->choice->load($this->keyword));


        return;
    }


    function get()
    {

        //$this->variables_thing->getVariables();



        $this->previous_state = $this->variables_thing->getVariable("state")  ;
        $this->refreshed_at = $this->variables_thing->getVariables("refreshed_at");

//var_dump($this->variables_thing);

//exit();


        //$this->previous_state = $this->variables_thing->choice->load($this->keyword);
//exit();
//            $this->previous_state = $this->thing->choice->current_node;

        if ($this->previous_state == false) {$this->previous_state = "X";}
//exit();

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->previous_state);

        if (!isset($this->requested_state)) { $this->requested_state = "X";}
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;


        $this->state = $this->previous_state;
//echo $this->state;
//exit();

        return;

    }


    function read()
    {
        //$this->thing->log("read");

//        $this->get();
        return $this->state;
    }



    function selectChoice($choice = null)
    {

        if ($choice == null) {
            return $this->state;

    //        $choice = 'off'; // Fail off.
        }


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".');

        $this->set($choice);


        //$this->thing->log('Agent "' . ucwords($this->keyword) . '" choice selected was "' . $choice . '".');

        return $this->state;
    }

    function makeSMS()
    {

        if (isset($this->sms_message)) {
            $this->thing_report['sms'] = $this->sms_message;
            return $this->sms_message;
        }

        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->sms_message = "EVACSIM IS " . strtoupper($t);
//        $sms_message .= " | Previous " . strtoupper($this->previous_state);
//        $sms_message .= " | Now " . strtoupper($this->state);
//        $sms_message .= " | Requested " . strtoupper($this->requested_state);
//        $sms_message .= " | Current " . strtoupper($this->base_thing->choice->current_node);
//        $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
//        $sms_message .= " | base nuuid " . strtoupper($this->variables_thing->thing->nuuid);

//        $sms_message .= " | another nuuid " . substr($this->variables_thing->uuid,0,4); 
        $this->sms_message .= " | nuuid " . substr($this->variables_thing->variables_thing->uuid,0,4); 


        if ($this->state == "off") {
            $this->sms_message .= " | TEXT EVACSIM ON";
        } else {
            $this->sms_message .= " | TEXT ?"; 
        }

        $this->thing_report['sms'] = $this->sms_message;
        return $this->sms_message;

    }


	private function respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;


$this->thing->log( $this->agent_prefix .'. Timestamp  ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );


		$choices = $this->variables_thing->thing->choice->makeLinks($this->state);
//$choices = false;
		$this->thing_report['choices'] = $choices;

$this->thing->log( $this->agent_prefix .'. Links made. Timestamp  ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->makeSMS();


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Shift state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

		$test_message .= '<br>Requested state: ' . $this->requested_state;

	    //$this->thing_report['sms'] = $sms_message;
		$this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is Evacsim.  A tool developed to be supportive of NSEM.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}

    function eventKnock() {

//        $outcomes = array("blue"=>"Not home when first canvassed-must be canvassed again.",
//                        "pink"=>"Have been notified of an order to evacuate",
//                        "red"=>"Notified & needs assistance to evacuate",
//                        "yellow"=>"Verified as being evacuated",
//                        "orange"=>"Notified & are refusing to evacuate.");

        $knocks = array("ring, ring.", "knock. knock.", "brring. brring.", "ring.", "Thud. Thud.", "Chimes.");

        $n = count($knocks);
//var_dump($n);
        $i= rand(1, $n) - 1;

        $knock = $knocks[$i];


        $responses = array("1 | child refusal | A child answers the door. No one else comes to the door.",
                             "1 | no response | There is no answer.  You knock twice more, and still no answer.",
                             "1 | support | You hear dogs barking inside.  A person answers who tells you there are 5 people at home, one uses a walking frame.",
                             "1 | refusal | It isn't clear how many people are in the unit.",
                            "1 | no response | You hear people inside, but no-one comes to the door.", 
                            "1 | refusal | It isn't clear how many people are in the unit.",
                            "1 | notified | It isn't clear how many people are in the unit.",
                            "1 | notified | 4 people are in the unit.",
                            "1 | notified | 3 people are in the unit."
                            );

        $n = count($responses);
//var_dump($n);
        $i= rand(1, $n) - 1;
        $response = $responses[$i];
//var_dump($i);
//var_dump($response);
//exit();
        $pieces = explode(" | ", $response);
//var_dump($pieces[0]);
//var_dump($pieces[1]);

$this->knock_weight = $pieces[0];
$this->knock_response = $pieces[1];
$this->knock_text = $pieces[2];

        //var_dump($k);
        
       // $response_text = $responses[0];
//var_dump($response_text);
//exit();

        $this->sms_message = "EVACSIM | " . $knock . " " . $this->knock_text . " | " . strtoupper($this->knock_response);




    }


    public function readSubject() 
    {
        $this->response = null;

        $keywords = array('off', 'on', 'knock', 'unit knock', 'block clear');

        $input = strtolower($this->subject);

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

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
//var_dump($pieces);
//exit();
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) 
                    {

                        case 'off':
                            $this->thing->log('switch off');
                            $this->selectChoice('off');
                            return;
                        case 'on':
                            $this->selectChoice('on');
                            return;

                        case 'knock':
                            $this->eventKnock();

                            return;



                        case 'next':


                        default:

                    }

                }
            }

        }


        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch($this->requested_state)
        {
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






	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}

    function discriminateInput($input, $discriminators = null)
    {


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

                $aliases['on'] = array('red','on');
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
                                                //echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                //echo "sum";
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
                        //echo $key, $value;

          if ( isset($max) ) {$delta = $max-$value; break;}
                        if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
                }


//                        echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}

?>

