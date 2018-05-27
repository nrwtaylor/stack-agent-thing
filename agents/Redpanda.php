<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Redpanda {

	function __construct(Thing $thing) {

		//echo "Agent__construct";
		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.

		// We need access to all the Thing classes (ie json and db) which 
		// we get by this invocation.
		$thingy = $thing->thing;
		$this->thing = $thing;

        $this->agent_prefix = 'Agent "Redpanda"';

        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . ".", "INFORMATION");


		// Playing with these will lead to framework devopment.  Allow for a 
		// space here to develop dispatcher.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		// This should be all we need.  At this point to must be
		// sanitized of non-stacker addresses.

		// Run a quick chat and raise an exception.
		if (strpos($this->to, "@")) {
			$temp_thing = new Nonnom($this->thing);
			$this->thing->flagGreen();
				
			throw new Exception('Nominal information.');
		}
		// some magic happers here to make the above happen.
		// and if I seek to optimize this I can see myself messing 
		// around with Thing in the first instance.
		// 
		// Remember magic here - don't mess with the stuff up above.


$this->node_list = array("start"=>array("useful"=>array("bonus"=>"100","250")), "useful maybe"=>array("wrong place", "wrong time"),"helpful"=>"awesome");	

		$this->sqlresponse = null;

		// This sounds like a silly variable to need.

		$this->response_format = "text no images";



		// If readSubject is true then it has been responded to.
		// Forget thing.



		//Seems like I'm not sure I need this line.  Won't touch it.
		//$json_data = $this->thing->readJson("variables");
		$json_data = $this->thing->json->json_data;

		//$t = $this->thing->json->getVariable(array("dispatcher"));

		// We are going to be looking for ... as much as we can.
		// Which is does thing have a null json setting.  Which means
		// that {} correctly maps to null.  
		//$variable = 'dispatcher:{"response_at":time()}';

		if ($json_data == null) {
			// No text in field.
			} else {
			// Text in the field
			// So extract existing setting
			}

		$this->readSubject();
		$thing_report = $this->respond();


			
			// Redpanda's job is once no other agent has been 
			// able to figure out what to do with a Thing.
			// Does something useful.

			$this->thing->flagGreen();

			$this->thing_report = $thing_report;

        $this->thing_report['log'] = $this->thing->log;


			return;

	}



	public function readSubject() {
		// No real reason to read the subject.

	return true;		
	}


	public function respond() {
		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("redpanda"), array(
			'log:{"start_at":' . $this->thing->json->time() . '}'
			));

		$thing_report = $this->chooseResponse();


		//$this->thing_report = array('thing' => $this->thing->thing, 'choices' => null, 'info' => 'This is a reminder.','help' => 'This is probably stuff you want to remember.  Or forget.');

        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'This is a reminder.';
        $this->thing_report['help'] = 'This is probably stuff you want to remember.  Or forget.';


		//$response = null;

	return $this->thing_report;
	}




	public function chooseResponse($i = NULL) {

		// Choose a response stochastically.

		//echo "chooseResponse()\r";
		if ($i == NULL) {

// Tweaked this for PRODUCTION
// Need to create a Stochastics class
//			 $i = NDie(20);
			$i = rand(1,20);
			}
	
		//%$i = NDie(20);

		//%$i = 1;

		//$this->testFunc();

//		$this->thing->test($i, 'redpanda', 'rolled D20 from');

		switch ($i) {
			case 1:
				//echo "sendKey";
				return $this->sendReminder();
				break;
			case 2:
				//echo "D2 - sendReminder";
				//$this->sendKey();
				return $this->randomComment();
			case 3:
				//echo "D2 - sendReminder";
				return $this->sendReminder();
				break;
			case 4:
				//echo "D2 - sendReminder";
				return $this->sendReminder();
				break;
			case 5:
				//echo "D2 - sendReminder";
				return $this->sendReminder();
				break;

			case 20:
				//echo "D20 - sendReminder";

				return $this->sendReminder();

				break;
			default:
                                return $this->randomComment();

			   //echo "i is not equal to 0, 1 or 2";
			}
		return $i;
	}



	public function sendReminder() {
		//echo "send reminder";
		$thing_report = new Reminder($this->thing);
		$this->thing->flagGreen();

		return $thing_report;

	}



	public function randomComment() {

                $thing_report = new Hey($this->thing);
                $this->thing->flagGreen();


	}	


}









?>
