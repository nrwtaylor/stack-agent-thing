<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Shift 
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "shift";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->node_list = array("off"=>array("on"=>array("off")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

        $this->get(); // Updates $this->elapsed_time;

        // And so at this point we have a timer model.

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()

		$this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

		$this->thing->log('Agent "Shift" running on Thing ' . $this->thing->nuuid . ".");
		$this->thing->log('Agent "Shift" received this Thing, "' . $this->subject .  '".') ;

		$this->readSubject();
		$this->respond();

		//echo '<pre> Agent "Shift" completed</pre>';

        $this->thing_report['log'] = $this->thing->log;
        //echo $this->thing_report['log'];


		return;

		}


    function set($requested_state = null)
    {

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array($this->keyword, "state"), $requested_state );
        $this->thing->json->writeVariable( array($this->keyword, "refreshed_at"), $this->current_time );

        $this->thing->choice->Choose($requested_state);

$this->thing->log("$requested_state is ". $requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

//var_dump($this->thing->thing);

//        echo $this->thing->choice->current_node;

$this->thing->log("Result of choice->load() ". $this->thing->choice->load($this->keyword));


        return;
    }


    function get()
    {

        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, $this->keyword);

        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_obj) {

            $thing = new Thing ($thing_obj['uuid']);

            $thing->json->setField("variables");
            $thing->previous_state = $thing->json->readVariable( array($this->keyword, "state"))  ;
            $thing->refreshed_at = $thing->json->readVariable( array($this->keyword, "refreshed_at"))  ;

            if ($thing->refreshed_at == false) {
                // Things is list sorted by date.  So this is the oldest Thing.
                // with a 'keyword' record.
                continue;
            } else {
                break;
            }

        }

        // See where we stand.

        if (!isset($this->request_state)) {
            $this->requested_state = 'X'; // Default request for signal (X)
        }

        // Redundant, but probably helpful when wanting confirmation.  And 
        // redundancy.  And a record of the state change
        // request.
        $this->set($this->requested_state);


        if ($thing->refreshed_at == false) {

            // No $this->keyword agent found.  So
            // the current Thing also becomes the base thing.

            $this->base_thing = $this->thing;

        } else {

            // The Thing already exists
            $this->base_thing = $thing;

        }



        $this->base_thing->json->setField("variables");

        $this->previous_state = $this->base_thing->json->readVariable( array($this->keyword, "state"))  ;
        $this->refreshed_at = $this->base_thing->json->readVariable( array($this->keyword, "refreshed_at") );

        $this->previous_state = $this->base_thing->choice->load($this->keyword);
//            $this->previous_state = $this->thing->choice->current_node;


        $this->base_thing->choice->Create($this->keyword, $this->node_list, $this->requested_state);
        $this->base_thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;


        $this->state = $this->previous_state;


        return;
    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        return $this->state;
    }



    function selectChoice($choice = null)
    {

        if ($choice == null) {
            $choice = 'off'; // Fail off.
        }


        $this->thing->log('Agent "' . $this->keyword . '" chose ' . $choice);

        $this->set($choice);


        $this->thing->log("Choice selected was " . $choice);

        return $this->state;
    }


	private function respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;

		$choices = $this->base_thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;

		$sms_message = "SHIFT IS " . strtoupper($this->state);
        $sms_message .= " | Previous " . strtoupper($this->previous_state);
        $sms_message .= " | Now " . strtoupper($this->state);
        $sms_message .= " | Requested " . strtoupper($this->requested_state);
        $sms_message .= " | Current " . strtoupper($this->base_thing->choice->current_node);
        $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        $sms_message .= " | base nuuid " . strtoupper($this->base_thing->nuuid);
        $sms_message .=  " | TEXT ?";


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Shift state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is a shift manager.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


    public function readSubject() 
    {
        $this->response = null;

        $keywords = array('on', 'off','next');

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
/*
                        if ( $this->thing->choice->isValidState($input) ) {

echo "valid state";
				$this->requested_state = $input;
                                $this->thing->choice->Choose($input);
                               
                                return $input;
                        }
*/





                        return "Request not understood";

                }

//echo "meepmeep";

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {
/*
                                                case 'stopwatch':    

                                                        if ($key + 1 > count($pieces)) {
                                                                //echo "last word is stop";
                                                                $this->stop = false;
                                                                return "Request not understood";
                                                        } else {
                                                                //echo "next word is:";
                                                                //var_dump($pieces[$index+1]);
                                                                $command = $pieces[$key+1];

								if ( $this->thing->choice->isValidState($command) ) {
                                                                	return $command;
								}
                                                        }
                                                        break;
*/

    case 'on':
        $this->selectChoice('on');
        return;
    case 'off':
        $this->selectChoice('off');
        return;
    case 'next':

        $choices = $this->base_thing->choice->makeLinks($this->state);
//        $this->thing_report['choices'] = $choices;

        //$this->thing->choice->Choose("foraging");
       // $a = $this->base_thing->choice->makeChoices();

$next = strtolower(array_pop($choices['words']));
//exit();

        $this->selectChoice($next);
        return;

    case 'mon':
        $this->selectChoice('mon');
        return;
    case 'sat':
        $this->selectChoice('sat');
        return;

    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }


// If all else fails try the discriminator.

    $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
    switch($this->requested_state) {
        case 'on':
            $this->selectChoice('on');
            return;
        case 'off':
            $this->selectChoice('off');
            return;
        //case 'reset':
        //    $this->reset();
        //    break;
        //case 'split':
        //    $this->split();
        //    break;
    }

    $this->read();




                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

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

                //echo "total count"; $total_count;
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

