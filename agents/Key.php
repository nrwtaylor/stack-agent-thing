<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Key {

	function __construct(Thing $thing, $agent_input = null) {

        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "key";

        $this->agent_prefix = 'Agent "Key" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.
		$this->thing = $thing;

        $this->uuid = $thing->uuid;
       	$this->to = $thing->to;
       	$this->from = $thing->from;
       	$this->subject = $thing->subject;
		$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . ".");
        $this->thing->log($this->agent_prefix . 'received this Thing, "' . $this->subject .  '".') ;

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.
		$this->readSubject();
		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . $milliseconds . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;


	}

	public function respond() {

		// Thing actions

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("key",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);

		$this->thing->flagGreen();

		$from = $this->from;
		$to = $this->to;

		//echo "from",$from,"to",$to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = $this->sqlresponse;

$message = "'keymanager' decided it was about time that you had a new
key to access Stackr. Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid\n\n\n<br> ";
$message .= '<img src="' . $this->web_prefix . 'thing/'.$uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';


$this->sms_message = "KEY | " . $this->web_prefix . "thing/$uuid/agent | TEXT [ FORGETALL | SHUFFLE ]";

		// Assemble a button set.

$node_list = array("key maintenance"=>array("happy","not happy"=>array("more","less")));

		$this->thing->choice->Create($node_list, "key maintenance");

//$choices = $this->thing->choice->getChoices();

		//$links = array("url"=>$urls, "link"=>$html_links, "button"=>$buttons);
		$choices = $this->thing->choice->makeLinks();
		//$html_button_set = $links['button'];

                     $this->thing_report['choices'] = $choices;


                        $this->thing_report['sms'] = $this->sms_message;
                        $this->thing_report['message'] = $message;
                        $this->thing_report['email'] = $message;
                        $this->thing_report['choices'] = $choices;
//                        $this->thing_report['info'] = 'SMS sent';



//echo $quoted_printable_button_set;

		//$db = new Database($this->uuid);	
//		$user_state = $this->thing->currentState();		

//		if ($user_state == "opt-in") {

//			$this->thing->email->sendGeneric($from,"keymanager",$subject,$message,$html_button_set);


                        $message_thing = new Message($this->thing, $this->thing_report);
                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


//		}
		//echo "Simulate email send";



		return;
	}



	public function readSubject() {


		$status = true;
	return $status;		
	}


	public function sendKey() {



	}



}









?>
