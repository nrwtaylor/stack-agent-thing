<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


ini_set("allow_url_fopen", 1);

class Shuffle {


	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {
    $this->agent_input = $agent_input;


	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];


		

		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;

//$this->thing_report['message'] = "question meep";

		$this->agent_name = 'shuffle';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("transit", "opt-in"));

		$this->thing->log( 'Agent "Shuffle" running on Thing ' .  $this->uuid . '');
		$this->thing->log( 'Agent "Shuffle" received this Thing "' . $this->subject .  '"');



        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("shuffle", "refreshed_at") );

        if ($time_string == false) {

            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("shuffle", "refreshed_at"), $time_string );

        }


//		$this->readSubject(); // No need to read subject 'translink' is pretty clear.

        $this->readSubject();

        echo "shuffle commented out";
        //$this->thing->shuffle();

        if ($this->agent_input == null) {
		    $this->thing_report = $this->respond();
        }
		$this->thing->log ('Agent "Shuffle" completed.');

		return;

		}

	public function helpShuffle() {



			//$this->message = "Thanks for the question mark.  Here are some things you can do.  The Management.";

                	$this->response = 'Generates a new identifier for your Things.' ;
 
		return;
	}



   function allShuffle()
    {

        // Getting memory error from db looking
        // up balance for null
        if ($this->from == "null@" . $this->mail_postfix) {
            $this->response = "Shuffle All requires an identity.";

            return;
        }

        // devstack paged input
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        $this->total_things = count($things);

        $start_time = time();

        $count = 0;
        shuffle($things);

        $start_time = time();

//        echo count ($this->total_things);

        while (count($things) > 1) {

            $thing = array_pop($things);

            if ($thing['uuid'] != $this->uuid) {

                $temp_thing = new Thing($thing['uuid']);
                $temp_thing->Shuffle();

                $count += 1;

            } else {

                //echo "match";

            }
        }


        $this->response = "Completed request for this Identity. Shuffled ". $count . " Things.";

        return;
    }


    private function weekShuffle() {
        $this->response = "Week shuffle not implemented.";
    }

    private function dayShuffle() {
        $this->response = "Day shuffle not implemented.";
    }

    private function hourShuffle() {
        $this->response = "Hour shuffle not implemented.";
    }

    private function thingShuffle(){

        //echo "shuffle commented out";
        $this->thing->shuffle();

        // And fix these pointers.  Now wrong.
        $this->uuid = $this->thing->uuid;
        $this->thing_report['thing'] = $this->thing->thing;


    }


	private function respond() {


		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;

		$from = "shuffle";



		//$message = $this->readSubject();

		//$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "shuffle");
		$choices = $this->thing->choice->makeLinks('shuffle');


		$this->thing_report['thing'] = $this->thing->thing;
		$thing_report['choices'] = $choices;

        $this->makeSMS();

        $this->thing_report['email'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);

        //$thing_report['info'] = $message_thing->thing_report['info'] ;

		$this->thing_report['help'] = 'Shuffle.' . $this->helpShuffle();

		return $this->thing_report;


	}

    public function makeSMS()
    {
        $this->sms_message = "SHUFFLE | " . $this->response;

        $this->thing_report['sms'] = $this->sms_message;


    }

	private function nextWord($phrase) {


	}

	public function readSubject() {

		$this->response = null;


		$keywords = array('shuffle', 'melt');

		$input = strtolower($this->subject);

		$prior_uuid = null;

		$pieces = explode(" ", strtolower($input));




                if (count($pieces) == 1) {

                        $input = $this->subject;
                        //echo str_word_count($this->subject);
			
                        if (is_string($this->subject) and strlen($input) == 1 ) {
				// Test for single ? mark and call question()
				$this->message = "Single question mark received";
				//echo "single question mark received";
                    $this->helpShuffle();
                    if (!isset($this->response)) {$this->response = "This agent shuffles UUIDs";}
                                return;
                        }
	        		//$this->message = "Request not understood";
    		    	$this->thing->shuffle();
                    $this->response = "This Thing was shuffled.";
                        return;

        	}

		// If there are more than one piece then look at order.

		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {				
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
						case '?':	

							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								//$this->stop = false;
                                $this->helpShuffle();
                                $this->response = "Question mark at end";

								return;
							} else {
								// Question mark was in the string somewhere.
								// Not so useful right now.
								return;
							}
							break;

                        case 'all':
                            $this->shuffleall();
                            if (!isset($this->response)) {
                                $this->response = "There was a problem shuffling all your Things.";
                            }
                            return;

                        case 'week':
                            $this->shuffleweek();
                            if (!isset($this->response)) {
                                $this->response = "Shuffled all your Things to the start of the week (Mon).";
                            }
                            return;

						default:
							//echo 'default';

					}

				}
			}

		}
		$this->response = "No Things were shuffled.";
		return $this->response;
	}



}




?>



