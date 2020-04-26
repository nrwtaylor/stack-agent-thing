<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
ini_set("allow_url_fopen", 1);

class Train {

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {
	//function __construct($arguments) {

//echo $agent_input;
//$this->head_code = $agent

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
		$this->character_thing = $thing;
		$this->agent_name = 'character';

        $this->thing_report = array('thing' => $this->thing->thing);


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 48; // Retain for at least 48 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = strtolower($thing->subject);

        $this->response = null;


		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("red"=>array("green"=>array("red","green"), "red"));

		$this->thing->log( '<pre> Agent "Train" running on Thing ' . $this->thing->nuuid . '</pre>');
		$this->thing->log( '<pre> Agent "Train" received this Thing "' . $this->subject .  '"</pre>');

		$this->trainsGet();

		// If this return true then no existing characters found.

//		$this->getTrain(); // Should load up current
        $this->head_code = null;
		$this->readSubject();
//exit();'
//        $this->getTrain($this->head_code); // Should work if head code is null too.

$this->thing->log ('Agent "Train" response is ' .$this->response . ".");

        if (($this->response == true)) {

	        $this->thing_report['info'] = 'No train response created.';
        	$this->thing_report['help'] = 'This is the train manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
            $this->thing_report['num_hits'] = $this->num_hits;

			$this->thing->flagGreen();

//            $this->thing->log( '<pre> Agent "Train" completed</pre>' );

        $this->thing->log( '<pre> Agent "Train" completed with response "'  .$this->response .'".</pre>' );

        $this->thing_report['log'] = $this->thing->log;


            return $this->thing_report;

		}

		$this->thing_report = $this->respond();

		$this->trainSet();
		$this->thing->log( '<pre> Agent "Train" completed with response "'  .$this->response .'".</pre>' );

        $this->thing_report['log'] = $this->thing->log;


		return;

    }


    public function nullAction() {

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("train", "action"), 'null' );

        $this->message = "TRAIN | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TRAIN | Request not understood. | TEXT SYNTAX";
        $this->response = true;

        return $this->message;
    }


	function trainSet() {


        $this->thing->log( '<pre> Agent "Train" called trainSet()</pre>' );
//var_dump($this->train_thing);
//exit();

        $this->train_thing->db->setFrom($this->from);
        $this->train_thing->json->setField("variables");

        $this->train_thing->json->writeVariable( array("train", "head_code") , $this->head_code  );

        $this->train_thing->json->writeVariable( array("train", "block") , $this->train_block  );

        $this->train_thing->json->writeVariable( array("train", "consist") , $this->consist  );
        $this->train_thing->json->writeVariable( array("train", "stops") , $this->stops  );
        $this->train_thing->json->writeVariable( array("train", "run_time") , $this->run_time );

        $this->train_thing->json->writeVariable( array("train", "run_at") , $this->run_at );

        $this->train_thing->json->writeVariable( array("train", "alias") , $this->alias );
        $this->train_thing->json->writeVariable( array("train", "jobs") , $this->jobs );
        $this->train_thing->json->writeVariable( array("train", "flag_token") , $this->flag_token  );


                //$this->age_thing->json->writeVariable( array("character", "earliest_seen"), $this->earliest_seen   );


//        $this->train_thing->flagGreen();

		}



	function trainsGet() {


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("train", "refreshed_at") );

        if ($time_string == false) {
            // Then this Thing has no character information
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("train", "refreshed_at"), $time_string );
        }

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch('train', 99);
        $things = $thing_report['things'];


        $this->sms_message = "";
        $reset = false;

        if ( $things == false  ) {

            // No character information store found.
            $this->resetTrain();

        } else {

            $this->trains = array();
			$this->seen_trains = array();
            foreach ($things as $thing) {

                $thing = new Thing($thing['uuid']);

				$this->train_uuid = $thing->uuid;

                $thing->json->setField("variables");
                $this->head_code = $thing->json->readVariable( array("train", "head_code") );


				if ( in_array($this->head_code, $this->seen_trains) ) {
					continue;
				} else {
					$this->seen_trains[] = $this->head_code;
				}

                $this->block_uuid = $thing->json->readVariable( array("train", "block") );


                $this->consist = $thing->json->readVariable( array("train", "consist") );
                $this->stops = $thing->json->readVariable( array("train", "stops") );


                $this->run_time = $thing->json->readVariable( array("train", "run_time") );
                $this->run_at = $thing->json->readVariable( array("train", "run_at") );
                $this->alias = $thing->json->readVariable( array("train", "alias") );
                $this->jobs = $thing->json->readVariable( array("train", "jobs") );
                $this->flag_token = $thing->json->readVariable( array("train", "flag_token") );

				$train = array("head_code"=>$this->head_code,
						"consist"=>$this->consist,
						"stops"=>$this->stops,
						"run_time"=>$this->run_time,
                        "run_at"=>$this->run_at,
						"alias"=>$this->alias,
						"jobs"=>$this->jobs,
						"flag_token"=>$this->flag_token);

                if (($this->head_code == false) or
                    ($this->block_uuid == false) or
                    ($this->consist == false) or
				    ($this->stops == false) or
                    ($this->run_time == false) or
                    ($this->alias == false) or
                    ($this->jobs == false) or
					($this->flag_token == false)
 					) {

					$this->thing->log('Agent "Train" found no existing train information');
//					$this->thing->log ( "No character info found.  Created a random character.");
//                                        $this->randomCharacter();
                } else {

					$this->age = $thing->thing->created_at;

                   	$train = array("head_code"=>$this->head_code,
				        "uuid"=>$this->train_uuid,
                        "consist"=>$this->consist,
                        "stops"=>$this->stops,
                        "run_time"=>$this->run_time,
                        "run_at"=>$this->run_at,
                        "alias"=>$this->alias,
                        "jobs"=>$this->jobs,
                        "flag_token"=>$this->flag_token,
						"age"=>$this->age);


                    // Successfully loaded most recent train Thing
					// and stored the other trains in an array
					$this->trains[] = $train;
                }
            }

			if (count($this->trains) == 0) {
				return true;
			}

		}
    	return $this->trains;
	}





	function trainReport() {


        $this->sms_message = "TRAIN";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

        $this->sms_message .= $this->head_code . ' | ';

        $this->sms_message .= "BLOCK " . $this->block_uuid . ' ';

        $this->sms_message .= "CONSIST " . $this->consist . ' ';
		$this->sms_message .= "STOPS " . $this->stops . ' ';
		$this->sms_message .= "RUNTIME " . $this->run_time . ' ';
        $this->sms_message .= "RUN AT " . $this->run_at['time'] . ' ';
		$this->sms_message .= "ALIAS " . $this->alias . ' ';
		$this->sms_message .= "JOBS " . $this->jobs . ' ';
		$this->sms_message .= "FLAG TOKEN " . $this->flag_token . ' | ';

        $this->sms_message .= "TEXT HELP";

		return;
	}

	function trainList() {

        $this->sms_message = "TRAIN > LIST";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";

//                $this->sms_message .= $this->name . ' | ';

		if ( count($this->trains) == 0) {
			$this->sms_message .= "No trains found. | TEXT NEW RANDOM TRAIN";
		} else {       
            //$this->sms_message .= $this->name . ' | ';
			$count = 0;
 	     	foreach($this->trains as $train) {
			    $count += 1;
                $this->sms_message .= "" . $train['head_code'] . '';
                $this->sms_message .= ' | ';

				if ( strlen($this->sms_message . ( count($this->trains) - $count ) . " more found. | TEXT HELP") > 159 ) {
					$this->sms_message = $this->sms_message . ( count($this->trains) - $count ) . " more found. | ";
					break;
				}
			}
            $this->sms_message .= "TEXT HELP";
		}
        return;
    }



    function trainSyntax() {

        $this->sms_message = "TRAIN > SYNTAX";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}

        $this->sms_message .= " | ";
        $this->sms_message .= '<qualifier> TRAIN <command> | ';

        $this->sms_message .= "TEXT [ TRAIN REPORT | HELP ]";

        return;

    }

    function trainInfo() {

        $this->sms_message = "TRAIN > INFORMATION";

        $this->sms_message .= " | ";
        $this->sms_message .= "The current train is " .$this->head_code . ' | ';
        $this->sms_message .= "The train age is " .$this->age . ' | ';
        $this->sms_message .= "TEXT [ CHARACTER REPORT | HELP ]";

        return;

    }


    function trainHelp() {

        $this->sms_message = "TRAIN | HELP";

        $this->sms_message .= " | ";
        $this->sms_message .= "Trains are Things which you can talk to.  There are player trains, and non-player trains.  Trains are a group of things with character...";
		$this->sms_message .= " | ";
        $this->sms_message .= "TEXT [ NEW RANDOM CHARACTER | WHATIS ]";

        return;
    }


// -----------------------

	private function respond() {

		//$this->thing_report = array('thing' => $this->thing->thing);

		// Thing actions
		$this->thing->flagGreen();


        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';

		// Generate email response.

		$to = $this->thing->from;

// Testing 
//	$to = 'redpanda.stack@gmail.com';

		$from = "train";

		//$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');
		$this->thing_report['choices'] = $choices;


		// Need to refactor email to create a preview of the sent email in the $thing_report['email']
		// For now this attempts to send both an email and text.

        $message_thing = new Message($this->thing, $this->thing_report);
	    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->thing_report['help'] = 'Train development.';

        $this->response = "Message sent";

		return $this->thing_report;


	}

	public function resetTrain () {

        $this->thing->log( '<pre> Agent "Train" called resetTrain()</pre>' );

        $this->sms_message = "Empty train created. | ";
        $this->count = 0;

        if ($this->head_code == null) {
            $this->head_code = '0O01';
        }

        $this->stops = "TBA";
        $this->consist = "Z";
        $this->jobs = "X";
        $this->run_time = "200";
        $this->run_at = array("day"=>"MON", "date"=>"9-15", "time"=>"0000");


        require_once('/var/www/html/stackr.ca/agents/block.php');
        $trainblock_thing = new Block($this->thing);
        $thing_report = $trainblock_thing->thing_report;

        $this->block_uuid = $trainblock_thing->uuid;



//echo $this->block_uuid;
//exit();



        $this->alias = "Train 1";
        $this->flag_token = "RED";


        $this->train_thing = new Thing(null);
        $this->train_thing->Create($this->from , 'train', 's/ reset train');

        $this->trainSet();

        // Flag reset train Thing Green and available for tasking.
        $this->train_thing->flagGreen();

        return;
    }


    function nameTrain ($name = null) {

                $this->thing->log( '<pre> Agent "Train" name applies to ' . $this->name . '</pre>' );


                $this->sms_message = 'CHARACTER | "' . $this->name . '" renamed "' . $name . '". | TEXT ' . strtoupper($name);
                $this->count = 0;

		if ($name == null) { 
            $this->name = "Ivor the Tank Engine"; 
        } else {
            $this->name = $name;
        }

		$this->trainSet();

        return;
    }


    public function randomTrain () {

        $this->thing->log( '<pre> Agent "Train" called randomTrain()</pre>' );


        $this->sms_message = "Random train. | ";
        $this->count = 0;

		$nominals = array('a','b','c');

        $adjectives = array('A', 'B', 'C','stupendous','quixotic','horrible','great','blonde','artificial','kinetic',
			'normal','awful','terrible','small','tiny','fantastic','fascinating','hilarious','deafening');

		$adjective = $adjectives[array_rand($adjectives)];
		$nominal = $nominals[array_rand($nominals)];


        $this->head_code = "0O05";

        $this->consist = ucwords( $adjective ) . ucwords( $nominal );
        $this->stops  = array("start"=>"Alpha", "stop 1"=> "Bravo", "stop 2" => "Charlie");
        $this->run_time = "60";
        $this->run_at = array("time"=>"0000");

        $this->alias = "Stalybridge to Piccadilly";

        $this->flag_token = "GREEN";

//$this->character_thing = $this->thing;

        $this->train_thing = new Thing(null);
        $this->train_thing->Create($this->from , 'train', 's/ new random train');

		$this->trainSet();

        $this->train_thing->flagGreen();

        return;

    }

    public function getHeadcode($input = null) {

        if ($input == null) {
            $input = strtolower($this->to . " " .$this->subject);
        }
        $pieces = explode(" ", strtolower($input));




        // Extracts the head code from the provided input.
        // Returns a blank code if no headcode OR multiple
        // head codes provided.

        //[A-Z]{2}\d{6}
//echo "<br>" . print_r($pieces) . "<br>";
        $pattern = "|\d{1}[A-Za-z]{1}\d{2}|";
        $count_matches = 0;
        foreach($pieces as $key=>$piece) {

            if ((preg_match($pattern, $piece, $m)) and (strlen($piece) == 4)) {

                $this->num_hits += 1;
                $count_matches += 1;
                $this->head_code = $piece;


                $this->thing->log('<pre>Agent "Train" set the headcode as ' . $this->head_code . '.</pre>');

                $this->thing->log('Agent "Train" spotted ' . $piece . ' which is a headcode match.');
                //echo $piece;
            }
            //echo $piece;
        }


        if ($count_matches == 1) {
            //$this->head_code = $pie;
            //echo "HEADCODE " . $this->head_code;

                $this->thing->log('<pre>Agent "Train" set the headcode as ' . $this->head_code . '.</pre>');


//            $this->getTrain($this->head_code); // Should work if head code is null too.



            // Drop through if to further processing
//echo "foo";

        } else {

//echo "bar" . $count_matches;
            // If a definitive head code isn't found
            // look for alias matches in the provided Thing input.
            $this->trainList();

            $matches = array();

            foreach($pieces as $key=>$piece) {

                $common_words = array('the');
                foreach ($this->trains as $train) {

$this->thing->log('<pre>Agent "Train" checking for ' . $train['alias'] . "</pre>");

                    $alias_array = explode(" ", strtolower($train['alias']));

                    foreach ($alias_array as $alias_word) {

                        if ($alias_word == $piece ) {
                            if (!in_array($piece, $common_words) ) {
                                $matches[] = $train;
                                $this->num_hits += 1;
                           }
                        }
                    }
                }
            }


//var_dump($matches);
//exit();

            $matches = array_unique($matches);

            if ( count($matches) == 1 ) {

                $this->thing->log( '<pre> Agent "Train" $this->head_code input ' . $this->head_code . '</pre>' );
//                $this->getTrain($matches[0]['head code']); 
                $this->thing->log( '<pre> Agent "Train" $this->head_code output ' . $this->head_code . '</pre>' );

                // Leave head_code set as is.

             } elseif ( count($matches) == 0 ) {

                $this->thing->log( '<pre> Agent "Train" did not match any trains</pre>' );
                $this->head_code = false;
                //$this->nullAction();

                //$this->response = true; // Flag error (no trains found).
                //return;

            } else {

               // $this->response = true; // Flag error (too many trains found).
                $this->head_code = true;

                //return;

            }

        }


        // At this point we have a headcode. or not.





    }

	public function getTrain() {

        // If a headcode is available.  Attempt to retrieve train by headcode.
        // If not found, then add a new train with the headcode.

        // If no 

        $this->thing->log( '<pre> Agent "Train" called getTrain()</pre>' );

        // If no search term provided, return current train

        if (count($this->trains) == 0) {return true;}


        if ($this->head_code == null) {
            // If there is no head code
            // Pull most recent train
			$train = $this->trains[0];

        } else {

            //$found_flag = false;
            foreach ($this->trains as $train) {

               if ($train['head_code'] == $this->head_code) {
                break;
                }
            }
        }
        //$this->train_thing = $train;

        $this->train_thing = new Thing($train['uuid']);


		return;
    }


  


	function rollAbility () {
		$arr = array('a','b','c','d');

		$minimum = 6;
		$sum = 0;

		foreach ($arr as $item) {
			$roll = rand(1,6);
			if ($roll < $minimum) {$minimum = $roll;}
			$sum = $sum + $roll;
		}

		$score = $sum - $minimum;

		return $score;
	}


	private function nextWord($phrase) {}



	public function readSubject() {

// Now need to edit this to extract train related instructions.
// Build "Block" and "Flag" agents.
// NRW Taylor 2017 Sep 23



        $this->thing->log( '<pre> Agent "Train" started to read "' . $this->subject . '"</pre>' );

		$this->num_hits = 0;


//$this->charactersGet(); // Load users characters into memory
        $input = strtolower($this->to . " " .$this->subject);

        $pieces = explode(" ", strtolower($input));


        $this->getHeadcode($input); // Sets $this->head_code 



//var_dump($this->head_code);
//echo "meep";
        // At this point we have a headcode. or not.
//exit();

 //       $this->getTrain($this->head_code); // Should work if head code is null too.

            $this->thing->log('<pre>Agent "Train" is doing keyword extraction.</pre>');


		$keywords = array('train', 'flag', 'flg', 'signal', 'token', 'gold', 'red', 
			'orange', 'yellow', 'blue', 'cyan', 'indigo', 'green',
            'new'
			);


		$input = strtolower($this->subject);

		$prior_uuid = null;

        // So at this point we have a head code and are attemping to 
        // extract instructions.

		$pieces = explode(" ", strtolower($input));

        // If there is just a one word command...

        if (count($pieces) == 1) {

            $this->thing->log('<pre>Agent "Train" checking keywords.</pre>');
            $input = $this->subject;

            if (ctype_alpha($this->subject[0]) == true) { // If the first character of the keyword is a letter
                // Strip out first letter and process remaning 4 or 5 digit number
                //$input = substr($input, 1);
			}


            if (is_numeric($this->subject) and strlen($input) == 5 ) { // If this is a five-digit number
				//echo "meep";
                //return $this->stopTranslink($input);
                //return $this->response;
            }

            if (is_numeric($this->subject) and strlen($input) == 4 ) { // If this is a four-digit number
                //return $this->busTranslink($input);
                //return $this->response;
            }

            // $this->getCharacter($this->subject);

            if ( $this->subject == 'train' ) { // If this is a four-digit number
				$this->trainReport();
                $this->response = "Train";
			    return;
            }


            // So the head_code will be set if there is a train
            // we want this to drop through to further intent extraction.
            //if (preg_match($pattern, $input, $m)) {
            //    // Train head code definitely found.
            //    $this->trainReport();
            //    $this->response = "Train";
            //    return;
            // }


/*
			if (count($matches) == 0) {$this->nullAction();
                        $this->thing->log( '<pre> Agent "Train" did not respond to any keywords and there are no train matches.</pre>' );

}

			if (count($matches) == 1) {
			    $this->trainReport();
                $this->thing->log( '<pre> Agent "Train" did not respond to any keywords and there was one train match.</pre>' );
			}

*/  


            $this->thing->log( '<pre> Agent "Train" did not respond to any keywords</pre>' );

			//$this->characterList();

//            return "Request not understood";
            // Fall through to multiple key word matching to pick up
            // At this point there is an assigned headcode
        }





		$this->thing->log('<pre>Agent "Train" is reading multiple words.</pre>');


		foreach ($pieces as $key=>$piece) {
//$this->thing->log($piece);
//exit();
			foreach ($keywords as $command)  {

				if (strpos(strtolower($piece),$command) !== false) {

//$this->thing->log("Matched command piece" . $piece . $command);

					switch($piece) {
                        case 'green':
                            $this->flag_token = "GREEN";
                            $this->trainSet();
                            return "green";
                            // Fall through
                        case 'red':
                            $this->flag_token = "RED";
                            $this->trainSet();
                            return "red";
                            // Fall through

						case 'name':	

							if ($key + 1 > count($pieces)) {
                                $this->thing->log('<pre>Agent "Train" matched "name" as last word.</pre>');

								//echo "last word is stop";
								$this->stop = false;
								return "Request not understood";
							} else {
                                $this->thing->log('<pre>Agent "Train" matched "name" in "' . $this->subject . '".</pre>');

								//echo "next word is:";
								//var_dump($pieces[$index+1]);
								$check = $pieces[$key+1]; // Have this return up to four words
								if ( ($check == "is") or ($check == "train") ) {
									$adjust = 1;
								} else {
									$adjust=0;
								}

								$slice = array_slice($pieces, $key + 1 + $adjust);
								$head_code = implode($slice, " ");
								$this->response = $this->nameTrain($head_code);
								return $this->response;
							}
							break;

                        case 'new':

							//$this->thing->log("new found");

                            if ($key + 1 > count($pieces)) { // Last word is new
                                $this->thing->log('<pre>Agent "Train" matched "new" as last word.</pre>');
                                $this->resetTrain();
                                return "Train reset";
                            } else {
                                $this->thing->log('<pre>Agent "Train" matched "new".</pre>');

                                if (strpos($input, 'random') !== false) {
                                    $this->thing->log('<pre>Agent "Train" matched "new" with "random".</pre>');
                                    //$this->thing->log("new followed by random");
	                                $this->randomTrain();
		                            $this->trainReport();
                                    return "Random train created";
                            }

                            switch ($pieces[$key + 1]) {
    							case 'random':
                                    $this->thing->log('<pre>Agent "Train" matched "new" followed by "random".</pre>');

	    							//$this->thing->log("new followed by random");
		    						$this->randomTrain();
                                    return "Train created";
				    		    } 
                            }

                            $this->thing->log('<pre>Agent "Train" matched "new" but nothing else.</pre>');

							$this->resetTrain();
							return "Train reset";
                            //echo 'bus';
                            break;

						case 'list':
							$this->trainList();

                            $this->thing->log('<pre>Agent "Train" matched "list".</pre>');


                            return "Train list created";
                        case 'info':
                            // Intentional fall through to 'information'.
                        case 'information':

                            $this->thing->log('<pre>Agent "Train" matched "information".</pre>');
                            $this->trainInformation($this->head_code);
                            return "Train information created";

						case 'report':

                            $this->thing->log('<pre>Agent "Train" matched "report".</pre>');
							$this->trainReport($this->head_code);
							return "Train report created";
							//echo 'bus';
							break;

						default:

                            $this->thing->log('<pre>Agent "Train" did not multiple-match.</pre>');
                            //$this->characterInfo();
							//echo 'default';

					}
				}
			}
		}

        $this->thing->log('<pre>Agent "Train" did not match anything in the subject "' . $this->subject . '".</pre>');


        // $this->thing->log("Agent Character did not match.");
        //$this->characterHelp();
        return true;
		return "Message not understood";
	}

}




?>



