<?php

// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';
//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

//require_once '../agents/watson.php';
//require_once '../agents/translink.php';



// Agents can make calls with stack privileges
//require_once '/var/www/html/stackr.ca/src/stackdb.php';



class Prompt {

	function __construct(Thing $thing) {

		$this->thing = $thing;
		$this->agent_name = 'prompt';

		// So I could call
		$this->test = false;
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;


$this->node_list = array('start'=>
			array('no time or date', 'time only', 'date only', 'time and date'));

		$this->node_list = array("start"=>array("scheduling"=>array("waiting"=>
					array("reminding"=>array("learning")))));

		echo '<pre> Agent "Prompt" running on Thing ';echo $this->uuid;echo'</pre>';



		// Probably an unnecessary call, but it updates $this->thing.
		// And we need the previous usermanager state.

		$this->thing->Get();

		$this->current_state = $this->thing->getState('prompt');

		echo '<pre> Agent "Prompt" previous usermanager state: ';echo $this->current_state;echo'</pre>';

		// Current base agent.  Refactor as null.php?

		$this->getSubject();
		$thing_report = $this->setSignals();

		$this->thing_report = $thing_report;
		echo '<pre> Agent "Prompt" completed</pre>';
		return;
	}

	public function setSignals() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 

		$thing_report = true;


		// Generate email response.

		// The getSubject has come up with the best assessment
		// of what the current_state is and what the request_state is.
		if ($this->test) {
//			echo '<pre> Agent "Usermanager" $this->current_state '; echo $this->current_state; echo ' </pre>';
//			echo '<pre> Agent "Usermanager" $this->requested_state '; echo $this->requested_state; echo ' </pre>';
			}





		if ($this->state_change) {

				switch ($this->requested_state) {
					case 'opt-in':

//						$agent = new Optin($this->thing);
						break;

//                echo '<pre> Agent "Usermanager" chose optin</pre>';

					case 'opt-out':
//						$agent = new Optout($this->thing);
//                echo '<pre> Agent "Usermanager" chose optout</pre>';
						
break;

					case null;
						// Tested case
						// Web view of 
						// thing/<34 char>/usermanager

				           	echo '<pre> Agent "Prompt" null received no action</pre>';

                           $this->thing->choice->Create('start', $this->node_list, "start");

                                // And then use the same tool to make the opt-out and credit 'temporary choices'
                                // as buttons.  Allows for other keywords to be injected.
                                $choices = $this->thing->choice->makeLinks('start');



// 1999-11-30 - No date?
// 2000 - response to ' June 23rd'?
// Relative date found - Weekday?  Tomorrow? 1 day.
// [am pm signal is lost - worth running an extractor.  Make an extractor agent]

//                              $test_message = $choices['url'];

                                $subject = $this->subject . ' - processed';

                                $message = "We received a request for a prompt from Stackr.
                                        <br>";

				$date_array = $this->extractDate();
				$this->date = $date_array;

$date_string = date('Y-m-d H:i:s', mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'])); 



				$message .= $date_string . "<br>";


				//$message .= $this->time_scale . "<br>";

				if ( isset($date_array['relative']) ) {

// date("Y-m-d H:i:s", $date_arra)

                                $relative_date =$date_array['relative']['year'] . '-' .
                                        $date_array['relative']['month'] . '-' .
                                        $date_array['relative']['day'] . ' ' .
                                        $date_array['relative']['hour'] . ':' .
                                        $date_array['relative']['minute'];


					$message .= "relative date found " . $relative_date ;
					$this->duration = $date_array['relative'];
				}

				$date =$date_array['year'] . '-' .
					$date_array['month'] . '-' .
					$date_array['day'] . ' ' .
					$date_array['hour'] . ':' .
					$date_array['minute'];


				$message .= "<br>";


					//$this->time_scale = $this->discriminateInput($input, array('minutes', 'days'));
					echo $this->time_scale . "<br>";


				$message .= "<br>";
				$message .= $date;
                                $message .= " 
                                        <br>
                                        Keep on stacking.

                                        ";

                                //echo $choices['button'];

                                $this->thing->email->sendGeneric($this->from,$this->agent_name,$subject, $message, $choices);
                                echo '<pre> Agent "Prompt" terms sent to '; echo $this->from; echo ' </pre>';

                                $thing_report = array('thing' => $this->thing->thing, 
					'choices' => $choices, 
					'info' => 'This is the opt-in agent responding to a valid opt-in request.',
					'help' => 'Reads dates in the subject and tries to best schedule stuff');

  


                                  		//$this->newuser();
						break;

					default:
 echo '<pre> Agent "Prompt" default chose newuser</pre>';

					  	$this->newuser();
                                                break;
					}

	

		}


		// NOTE THAT IT IS REALLY EASY TO CREATE A NEW AGENT TO 'PASS' THE
		// THING TO.
		//		echo '<pre> Agent created a Optout agent</pre>';
		//			$temp_thing = new Optout($this->thing);
		//
		//			// Whereas in the new scheme it would look like this.
		//			$this->thing->choice->Choose("opt-out");
		//			return;


		// aka ... Fast | Slow?

		if (isset($agent)) {
			$thing_report = $agent->thing_report;
		} else {
			$thing_report = array('thing'=>false);
		}

		return $thing_report;
	}



	public function getSubject() {


//$this->node_list = array("start"=>array("scheduling"=>array("waiting"=>
  //                                      array("reminding"=>array("learning"))));


		// What do we know at this point?
		// We know the nom_from.
		// We have the message.
		// And we know this was directed towards usermanager (or close).

		// So starting with nom_from.
		// Two conditions, we either know the nom_from, or we don't.

		//$status = false;

		$this->state_change = false;

		$input = strtolower($this->to . " " .$this->subject);

// First see what we have on record for this alias.
// Need to decide whether this is a stack call, or whether to create
// a Thing here.

// If it is a new User we will need a Thing.
// If it is an opted-out user, we will need to log a request

		$this->current_state = $this->thing->getState($this->agent_name);

		switch ($this->current_state) {

			case 'start':

				$this->time_scale = $this->discriminateInput($input, array('minutes', 'days'));

				$this->requested_state = $this->time_scale;

//				$this->requested_state = $this->discriminateInput($input, array('opt-in', 'opt-out'));
//				$this->thing->choice->Choose($this->requested_state);

//				if ($this->requested_state != $this->current_state) {$this->state_change = true;}

				break;

			case 'scheduling':

				$this->requested_state = $this->discriminateInput($input, array('opt-in', 'opt-out'));
 				$this->thing->choice->Choose($this->requested_state);

				if ($this->requested_state != $this->current_state) {$this->state_change = true;}

				break;

			case 'waiting':

				$this->requested_state = $this->discriminateInput($input, array('opt-in', 'opt-out'));

                                $this->thing->choice->Choose($this->requested_state);

                                if ($this->requested_state != $this->current_state) {$this->state_change = true;}

				break;

			case 'reminding';
				//$this->state_change = true;
 				//$this->thing->choice->Choose("new user");
				break;

			case 'learning';
				//$this->state_change = true;
                                //$this->thing->choice->Choose("new");
                                break;

			default:

		        $this->thing->json->setField("settings");
              		 $this->thing->json->writeVariable(array("prompt",
                        "received_at"),  $this->thing->json->time()
                        );

//$date_string = date('Y-m-d H:i:s', mktime($date_array['hour'], $date_array['minute'], $date_array['second'], $date_array['month'], $date_array['day'], $date_array['year'])); 

//$this->date;
//$this->duration;


                        $this->thing->json->setField("variables");
                         $this->thing->json->writeVariable(array("prompt",
                        "trigger_at"),  $this->thing->json->time()
                        );


				$this->state_change = true;
				$this->thing->choice->Choose("start");

	$this->extractDate();

			}

		return;
	}

	function extractDate() {

		//	$test_text = "I was born on 26 march 1975 at 23:56:2";
		//$test_text = "in 15 minutes remind me";
		$test_text = "remind me at 2pm in 2 days";
		$test_text = $this->subject;

		$a = date_parse($test_text); 

		return $a;
	}

	function kill() {
		return $this->thing->Forget();
	}

	function discriminateInput($input, $discriminators = null) {

		$default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

		if (count($discriminators) > 4) {
			$minimum_discrimination = $default_discriminator_thresholds[4];
		} else {
			$minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
		}

		//$input = "optout opt-out opt-out";

		if ($discriminators == null) {
			$discriminators = array('minutes', 'hours');
		}	

		$aliases = array();

		$aliases['minutes'] = array('m','mins','mns','minits');
		$aliases['hours'] = array('hours','h','hr','hrs','hsr');
			
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
