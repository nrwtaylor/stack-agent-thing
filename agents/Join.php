<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Join {

	public $var = 'hello';


    function __construct(Thing $thing) {
	//function __construct($arguments) {

		//echo $arguments;
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];



// STiCKY FOUR DIGIT CODE GENERATE.
// JOIN AND LEAVE not yet created.



		$this->thing = $thing;
		$this->agent_name = 'group';
		$this->thing_report['thing'] = array('thing' => $this->thing->thing);

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 4; // Retain for at least 4 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;



		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log( '<pre> Agent "Join" running on Thing ' . $this->uuid . '</pre>' );
		$this->thing->log( '<pre> Agent "Join" received this Thing "' . $this->subject . '"</pre>');

		// Read the group agent variable
                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("group", "refreshed_at") );

                if ($time_string == false) {
			// Then this Thing has no group information
                        //$this->thing->json->setField("variables");
                        //$time_string = $this->thing->json->time();
                        //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
                }

                $this->thing->json->setField("variables");
                $this->group_id = $this->thing->json->readVariable( array("group", "group_id") );
		
                if ($this->group_id == false) {
			// No group_id found on this Thing either.
                        //$this->startGroup();


                } 
		//else {$this->group_id = $group_id;}

// Initialize message token
                       $token_thing = new Tokenlimiter($this->thing, 'message');
		$this->token = $token_thing->thing_report['token'];

//echo $token_thing->thing_report['token'];
//$dev_overide = null;
//exit();       

//                        if ( ($token_thing->thing_report['token'] == 'sms' ) or ($dev_overide == true) ) {




		$this->readSubject(); // Extract possible responses.
		$this->thing_report = $this->respond();


		//$this->PNG(); // Red dot/green dot.




		$this->thing->log( '<pre> Agent "Join" completed</pre>' );

		return;

		}

	public function joinGroup($group = null) {

		//if ($group == null) {
			$group_thing = new Group($this->thing, "join ".$group);
			$group = $group_thing->thing_report['group'];

		//}

//                // Read the group agent variable
  //              $this->thing->json->setField("variables");

    //            $time_string = $this->thing->json->time();
      //          $this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string);




        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("group", "group_id"), $group );

		$this->sms_message = "Joined group " . $group;
		$this->message = "Joined group " . $group;


                return $this->message;
        }





// -----------------------

	private function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

        // $this->thing_report['num_hits'] = $this->num_hits;

		// Generate email response.

		$to = $this->thing->from;
		$from = "join";
		$message = $this->readSubject();


		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');

        $thing_report['choices'] = $choices;

        if (!isset($this->sms_message)) {$this->sms_message = "No group.";}

		$this->sms_message = "JOIN | " . $this->sms_message . " | TEXT LEAVE";

        $this->thing_report['sms'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'];

    	$this->thing_report['thing'] = $this->thing->thing;
	    $this->thing_report['choices'] = $choices;
	    $this->thing_report['help'] = 'This is the group manager.  NEW.  JOIN <4 char>.  LEAVE <4 char>.';
	    $this->thing_report['log'] = $this->thing->log;

    	return $this->thing_report;

	}

	private function nextWord($phrase)
    {

	}

	public function readSubject()
    {
		$this->response = null;

		$keywords = array('join', 's/join', 'jn', 'j');


		// Make a haystack.  Using just the subject, because ...
		// ... because ... I don't want to repeating an agents request
		// and creating some form of unanticipated loop.  Can 
		// change this when there is some anti-looping in the path
		// following.

		$input = strtolower($this->subject);

		$prior_uuid = null;


		// Split into 1-grams.
		$pieces = explode(" ", strtolower($input));

		// Keywording first
                if (count($pieces) == 1) {

			if ( strtolower($input) == 'join' ) {

				$this->thing->db->setUser($this->from);
				$thingreport = $this->thing->db->variableSearch(null, 'group', 1);
				$things = $thingreport['things'];

				if ( count($things) == 0 ) {
					$this->sms_message = "No group information found";
					$this->message = "No group information found";
					return;	
	//no group information found
				} else {

					foreach ($things as $key=>$thing) {
			$uuid = $thing['uuid'];
			$group_thing = new Thing ($uuid);


                $this->group_id = $group_thing->json->readVariable( array("group", "group_id") );

		$this->joinGroup($this->group_id);


				// Use latest group only
				return;

					}

				}
			}


    			if (ctype_alnum($input) and strlen($input) ==4 ) {
				$this->response = $input;

				// Check the response to a join request.


                	        $group_thing = new Group($this->thing, 'screen'); // Will pass the '4alphanumber' character in the Thing.

                        	$thing_report = $group_thing->thing_report;

			//echo $group_thing->num_hits;
			//echo 
			//$thing_report['choices'] = false;
			//$thing_report['info'] => 'Group responsiveness request sent to ' . $input);

				$this->num_hits = $this->thing_report['num_hits'] = $thing_report['num_hits'];

        	   		$this->thing->log( "Agent '" . $this->agent_name . "' says num_hits = " . $thing_report['num_hits']  );

				if ($this->num_hits >= 1 ) {

					$this->sms_message = "Join request received";
					// Group join request
					$group_thing = new Group($this->thing); 
					// Will pass the '4alphanumber' character in the Thing.  For action.

				}

				return "Agent '" . $this->agent_name . "' says numhits: " . $thing_report['num_hits'];

    			}

                        if (ctype_alpha($this->subject[0]) == true) {
                                // Strip out first letter and process remaning 4 or 5 digit number
                                $input = substr($input, 1);
			}


                        if (is_numeric($this->subject) and strlen($input) == 5 ) {
                                //return $this->response;
                        }

                        if (is_numeric($this->subject) and strlen($input) == 4 ) {
                                //return $this->response;
                        }

                        return $this->agent_name . " request not understood: " . $input;

        	}



		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {				
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
						case 'join':	

							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								$this->group = false;
								return "Request not understood";
							} else {
								//echo "next word is:";
								$this->group = $pieces[$key+1];
								$this->response = $this->joinGroup($this->group);
								return $this->response;
							}
							break;

						case 'new':
							$this->response = $this->startGroup();
							return $this->response;
							//echo 'bus';
							//break;
                                                case 'start':
                                                        $this->response = $this->startGroup();
                                                        return $this->response;
                                                        //echo 'bus';
                                                        //break;


						default:

							//echo 'default';

					}

				}
			}

		}
		return "Message not understood";
	}



        public function PNG() {
// Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

//I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and tes$
//No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my inluded files...
//So beware of your included files!
//Make sure they are not encoded in UTF or otherwise in UTF without BOM.
//Hope it save someone's time.

//http://php.net/manual/en/function.imagepng.php

//header('Content-Type: text/html');
//echo "Hello World";
//exit();

//header('Content-Type: image/png');
//QRcode::png('PHP QR Code :)');
//exit();
                // here DB request or some processing

//		if ($this->group_id == null) {
//			$this->startGroup();
//		}

                $codeText = "group:".$this->group_id;

                ob_clean();
                ob_start();

                QRcode::png($codeText,false,QR_ECLEVEL_Q,4); 
                $image = ob_get_contents();

                ob_clean();
// Can't get this text editor working yet 10 June 2017

//$textcolor = imagecolorallocate($image, 0, 0, 255);
// Write the string at the top left
//imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

$this->thing_report['png'] = $image;
//echo $this->thing_report['png']; // for testing.  Want function to be silent.

                return $this->thing_report['png'];
                }


}




?>



