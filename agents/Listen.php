<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Listen {

	public $var = 'hello';


    function __construct(Thing $thing)
    {

		$this->thing = $thing;
		$this->agent_name = 'listen';

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

		$this->thing->log( '<pre> Agent "Listen" running on Thing ' . $this->uuid . '</pre>' );
		$this->thing->log( '<pre> Agent "Listen" received this Thing "' . $this->subject . '"</pre>');

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

			$this->findGroup();

                } 
		//else {$this->group_id = $group_id;}

		$this_message = "";


                $this->thing->log( '<pre> Agent "Listen" group setup completed</pre>' );

		$this->readSubject(); // Extract possible responses.
		$this->thing_report = $this->respond();

		//$this->PNG(); // Red dot/green dot.

		$this->thing->log( '<pre> Agent "Listen" completed</pre>' );

		return;

		}

        function findGroup() {

//$this->thing->log( '<pre> Agent "Listen" called findGroup() </pre>' );
$this->thing->log( '<pre> Agent "Listen" looking for contextually close groups</pre>' );


                $group_thing = new Group($this->thing, "find");
		$this->group_id = $group_thing->thingreport['groups'][0];

$this->thing->log( '<pre> Agent "Listen" found a group nearby called ' . $this->group_id . ' </pre>' );


	        return;
	}


	public function joinGroup($group = null) {

$this->thing->log( '<pre> Agent "Listen" called joinGroup() </pre>' );

		$join_agent = new Group($this->thing, "join " . $group);

                // Read the group agent variable
 //               $this->thing->json->setField("variables");
//
//                $time_string = $this->thing->json->time();
//                $this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string);




  //              $this->thing->json->setField("variables");
    //            $this->thing->json->writeVariable( array("group", "group_id"), $group );

		$this->sms_message = "Joined group " . $group;
		$this->message = "Joined group " . $group;

		$this->sms_message = $join_agent->thing_report['sms'];
		$this->message = $this->sms_message;

                return $this->message;
        }



	function listenGroup() {

//$this->thing->log( '<pre> Agent "Listen" called listenGroup() </pre>' );

		$listen_agent = new Group($this->thing, "listen:". $this->group_id);
//$this->thing->log("listen.php");
$things = $listen_agent->thing_report['things'];

echo $this->group_id;
//$this->thing->log("listening");

		$this->sms_message = count($listen_agent->thing_report['things']);

		$tasks = "";
		foreach ($things as $thing) {

			$tasks .= $thing['task'];
			$this->message .= $thing['task'];
			$this->sms_message .= ' | "' . $thing['task'] . '" ' . number_format(time() - strtotime($thing['created_at']) ) . "s ago.";

            //copied to group
		}

		$this->thing->log( '<pre> Agent "Listen" heard ' . $tasks . '</pre>' );

	}


// -----------------------

	private function respond() {

$this->thing->log( '<pre> Agent "Listen" respond() </pre>' );


		// Thing actions
		$this->thing->flagGreen();


		$this->thing_report['num_hits'] = $this->num_hits;


		// Generate email response.

		$to = $this->thing->from;

		$from = "listen";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');

        $thing_report['choices'] = $choices;


		$this->sms_message = "LISTEN | " . $this->sms_message . " | TEXT STOP";
		$this->thing_report['sms'] = $this->sms_message;

        //$message_thing = new Message($this->thing, $this->thing_report);
        //$this->thing_report['info'] = $message_thing->thing_report['info'] ;

		$this->thing_report['info'] = "Listen request was handled by Group";


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
        $this->thing->log( '<pre> Agent "Listen" readSubject(), "' . $this->subject . '"</pre>' );
		$this->response = null;

		$keywords = array('listen', 'listne', 's/listen', 'ln', 'l');


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

			if ( strtolower($input) == 'listen' ) {

                $this->thing->log( '<pre> Agent "Listen" keyword heard </pre>' );

				$this->listenGroup();
				$this->message = $this->agent_name . " listenGroup() call: " . $input;
				$this->thing->log( '<pre> Agent "Listen" found 1 piece ' . $input . '</pre>' );

                return $this->agent_name . " listenGroup() called: " . $input;
			}

			$this->message .= $this->agent_name . " request not understood: " . $input;
            return $this->agent_name . " request not understood: " . $input;

        }

		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
						case 'listen':	

							if ($key + 1 > count($pieces)) {
								//echo "last word is stop";
								$this->group = false;
								$this->message .= "Request not understood. ";
								return "Request not understood";
							} else {
								//echo "next word is:";
								//var_dump($pieces[$index+1]);
								$this->group = $pieces[$key+1];
								$this->response = $this->joinGroup($this->group);

                                $this->thing->log( '<pre> Agent "Listen" heard ' . $piece . '</pre>' );

								return $this->response;
							}
							break;



						default:

							//echo 'default';

					}

				}
			}

		}
		$this->message .= "Request not understood. ";

		return "Message not understood";
	}

}


?>



