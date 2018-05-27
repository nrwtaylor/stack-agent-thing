<?php
namespace Nrwtaylor\StackAgentThing;


class Latency {

	public $var = 'hello';


    function __construct(Thing $thing) {

                $this->thing = $thing;
                $this->agent_name = 'latency';

                $this->queue_time = $this->thing->elapsed_runtime();

                $this->start_time = $this->queue_time;

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

            $this->created_at = $thing->thing->created_at;

        $this->current_time = $this->thing->json->time();


                $this->node_list = array("ping"=>array("pong"));

                $this->thing->log('<pre> Agent "Latency" running on Thing ' . $this->thing->nuuid . '.</pre>',"INFORMATION");



                // Probably an unnecessary call, but it updates $this->thing.
                // And we need the previous usermanager state.

                $this->thing->Get();

                $this->current_state = $this->thing->getState('usermanager');





		// create container and configure
		$this->api_key = $this->thing->container['api']['watson'];


		$this->readSubject();
		
		$this->thing_report = $this->respond();

		$this->thing->log('Agent "Latency" ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time)."ms.", "OPTIMIZE");

        $this->thing_report['log'] = $this->thing->log;


		return;

		}





// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "latency";
		
		$subject = 's/pingback '. $this->current_state;	

		$message = 'Latency checker.';

		//$email->sendGeneric($to,$from,$this->subject, $message);
		//$thing->thing->email->sendGeneric($to,$from,$this->subject, $message);

		$received_at = strtotime($this->thing->thing->created_at);

		//$ago = Thing::human_time ( time() - $received_at );

        $ago = $this->thing->human_time ( time() - $received_at );


		$this->sms_message = "LATENCY";


        $this->sms_message .= " | qtime " . number_format($this->queue_time) . "ms";

        $rtime = $this->thing->elapsed_runtime() - $this->start_time;
        $this->sms_message .= " | rtime " . number_format($rtime). "ms"; 

        $this->sms_message .= " | etime " . number_format($this->thing->elapsed_runtime()). "ms"; 

		$this->sms_message .= " | TEXT PING";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;

		//$this->thing_report['choices'] = false; 


                $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


                $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		

//		$this->thing->email->sendGeneric($to,"ping",$subject,$message);



		//$this->thing->log( '<pre> Agent "Latency" sent a message to ' . $this->thing->from . '</pre>');

	
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
