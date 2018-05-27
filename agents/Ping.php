<?php
namespace Nrwtaylor\StackAgentThing;

//require_once '/var/www/html/stackr.ca/agents/message.php';
//echo "Watson says hi<br>";

class Ping {
	

	public $var = 'hello';


    function __construct(Thing $thing) {

                $this->thing = $thing;
                $this->agent_name = 'ping';

 		$this->thing_report  = array("thing"=>$this->thing->thing);

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

                $this->node_list = array("ping"=>array("pong"));

                $this->thing->log('Agent "Ping" running on Thing ' . $this->thing->nuuid . '.', "INFORMATION");



                // Probably an unnecessary call, but it updates $this->thing.
                // And we need the previous usermanager state.

                $this->thing->Get();

                $this->current_state = $this->thing->getState('usermanager');





		// create container and configure
		$this->api_key = $this->thing->container['api']['watson'];


		$this->readSubject();
		
		$this->thing_report = $this->respond();

		$this->thing->log('Agent "Ping" completed.', "INFORMATION");

        $this->thing_report['log'] = $this->thing->log;


		return;

		}





// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "ping";
		
		$subject = 's/pingback '. $this->current_state;	

		$message = 'A message from ' . $this->thing->from . ' pinged us.  At least the ping agent is working.';

		//$email->sendGeneric($to,$from,$this->subject, $message);
		//$thing->thing->email->sendGeneric($to,$from,$this->subject, $message);

		$received_at = strtotime($this->thing->thing->created_at);

		//$ago = Thing::human_time ( time() - $received_at );

        $ago = $this->thing->human_time ( time() - $received_at );


		$this->sms_message = "PING | A message from this Identity pinged us.";

		if ($this->current_state == null) {
			$this->sms_message .= " | No state found.";
		} else {
			$this->sms_message .= " | State:" . $this->current_state;
		}

		$this->sms_message .= " | Received " . $ago . " ago.";

		$this->sms_message .= " | TEXT WATSON";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;

		//$this->thing_report['choices'] = false; 


                $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


                $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		

//		$this->thing->email->sendGeneric($to,"ping",$subject,$message);



//		$this->thing->log( '<pre> Agent "Ping" sent a message to ' . $this->thing->from . '</pre>');

	
	//	$this->thing_report = array('thing'=>$this->thing, 'keyword'=>'pingback', 'info'=>'Ping agent pinged back', 'help'=>'Useful for checking the stack.');

//                $this->thing_report['thing'] = $this->thing; 
$this->thing_report['keyword'] = 'pingback';
//$this->thing_report['info'] = 'Ping agent pinged back';
$this->thing_report['help'] = 'Useful for checking the stack.';



		return $this->thing_report;


	}



	public function readSubject() {

		return;

	
	}






}




return;
