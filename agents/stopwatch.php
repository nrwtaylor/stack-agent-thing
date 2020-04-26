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

class Stopwatch 
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

 $this->node_list = array("stop"=>array("start"=>array("split","stop"),"reset"),"reset");
$this->thing->choice->load('stopwatch');

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




//$this->thing->choice->Create('stopwatch', $this->node_list, 'stop');

//$this->thing->choice->Choose("midden work");

		echo '<pre> Agent "Stopwatch" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Stopwatch" received this Thing "';echo $this->subject;echo'"</pre>';


	// Read the elapsed time.  Or start.

 //               $this->current_time = $this->thing->json->time();

//	echo $this->read(); // Updates $this->elapsed_time;

		// Read the subject as passed to this class.

//	echo '<pre> Agent "Stopwatch" start state is ';
/*
	$this->state = $thing->choice->load('stopwatch'); //this might cause problems
	//echo $this->thing->getState('usermanager');
	echo $this->state;
	echo'"</pre>';
*/

		//$balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
       		//$t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem

		//$this->thing->account['token']->Credit(1);




		$this->readSubject();
		$this->respond();

/*
        echo '<pre> Agent "Stopwatch" end state is ';
        $this->state = $thing->choice->load('stopwatch');
        //echo $this->thing->getState('usermanager');
        echo $this->state;
        echo'"</pre>';
*/


		echo '<pre> Agent "Stopwatch" completed</pre>';

		return;

		}





    function set()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // Don't update the db variable at this point, because
        // the stopwatch command is not known ie stop, split, etc

        $this->thing->json->setField("variables");
        $this->thing->json->readVariable( array("stopwatch", "elapsed"), $this->elapsed_time );
        $this->thing->json->readVariable( array("stopwatch", "refreshed_at"), $this->current_time );
        $this->thing->choice->save('stopwatch', $this->state);

        return;
    }


    function get()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // See if a stopwatch record exists.
        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'stopwatch');

var_dump($findagent_thing->thing_report);
exit();
        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing) {

            $thing->json->setField("variables");
            $thing->elapsed_time = $this->thing->json->readVariable( array("stopwatch", "elapsed"))  ;

            if (($thing->refreshed_at == false) or ($thing->elapsed_time == false)) {
                continue;
            } else {
                break;
            }

        }

        // See where we stand.

        if (($thing->refreshed_at == false) or ($thing->elapsed_time == false)) {
            // Nothing found.

            $this->stopwatch_thing = $this->thing;

            $this->thing->json->writeVariable( array("stopwatch", "refreshed_at"), $this->current_time );
            $this->elapsed_time = 0;
            $this->refreshed_at = $this->current_time;
            $this->state = 'stop';


        } else {

            $this->stopwatch_thing = $thing;

            $this->stopwatch_thing->json->setField("variables");
            $this->elapsed_time = $this->stopwatch_thing->json->readVariable( array("stopwatch", "elapsed"))  ;

            $this->refreshed_at = $this->stopwatch_thing->json->readVariable( array("stopwatch", "refreshed_at") );
            $this->previous_state = $this->stopwatch_thing->choice->name;

            $this->state = $this->previous_state;
        }

        return;
    }


    function read()
    {
        $this->thing->log("read");

        $this->get();
        return $this->elapsed_time;
    }



    function reset()
    {
        $this->thing->log("reset");

        $this->get();
        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->thing->choice->Create('stopwatch', $this->node_list, 'stop');
/*
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("stopwatch", "refreshed_at"), $this->current_time);
        $this->thing->json->writeVariable( array("stopwatch", "elapsed"), $this->elapsed_time);
*/
        $this->thing->choice->Choose('stop');

        $this->set();

        return $this->elapsed_time;
    }

    function stop()
    {
        $this->thing->log("stop");
        $this->get();
        $this->thing->choice->Choose('stop');
        $this->set();
//                $this->elapsed_time = time() - strtotime($time_string);
        return $this->elapsed_time;
	}

    function start() 
    {
        $this->thing->log("start");

        $this->get();

echo "start";
echo $this->previous_state;

		if ($this->previous_state == 'stop') {
            $this->thing->choice->Choose('start');
            $this->state = 'start';
            $this->set();
            return;
		}

		if ($this->previous_state == 'start') {

            //echo $this->current_time;
            //ech
            $t = strtotime($this->current_time) - strtotime($this->refreshed_at);

			$this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->set();
            return;
		}

            $this->thing->choice->Choose('start');
            $this->state = 'start';
            $this->set();
            return;


        return null;
    }

	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
/*
		// Stop
		// Respond with elapsed time.
		// Start
		// Respond with elapsed time.
		// Reset
		// Set elapsed time to 0.

		switch ($this->requested_state) {
 		   case 'stop':
        $this->stop();
        break;
    case 'start':
        $this->start();
        break;
    case 'reset':
        $this->reset();
        break;

    case 'split':
        $this->split();
        break;


    default:
       $this->read();
}
*/
		// Generate email response.

		$to = $this->thing->from;
		$from = "stopwatch";

		echo "<br>";

		$choices = $this->thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;
		echo "<br>";
		//echo $html_links;

//$interval = date_diff($datetime1, $datetime2);
//echo $interval->format('%R%a days');

		$sms_message = "STOPWATCH | " . $this->elapsed_time . " | " . $this->state .  " | TEXT ?";

//echo $sms_message;

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Stopwatch state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is a stopwatch.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


    public function readSubject() 
    {
        $this->response = null;

        $keywords = array('stop', 'start', 'lap', 'reset');

        $input = strtolower($this->subject);

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'stopwatch') {
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

echo "meepmeep";

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

    case 'start':
        $this->start();
        break;
    case 'stop':
        $this->stop();
        break;
    case 'reset':
        $this->reset();
        break;
    case 'split':
        $this->split();
        break;

    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }


// If all else fails try the discriminator.

    $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
    switch($this->requested_state) {
        case 'start':
            $this->start();
            break;
        case 'stop':
            $this->stop();
            break;
        case 'reset':
            $this->reset();
            break;
        case 'split':
            $this->split();
            break;
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
                        $discriminators = array('start', 'stop', 'reset', 'lap');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['start'] = array('start','sttr','stat','st', 'strt');
                $aliases['stop'] = array('stop','stp');
                $aliases['reset'] = array('rst','reset','rest');
                $aliases['lap'] = array('lap','laps','lp');



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
                                                echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                echo "sum";
                                        }
                                }
                        }

                }

                echo "total count"; $total_count;
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


                        echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';


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

