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



	public function readSubject()
    {
		// No real reason to read the subject.
    	return true;
	}

	public function respond()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("redpanda"), array(
            'log:{"start_at":' . $this->thing->json->time() . '}'
            ));

        $thing_report = $this->chooseResponse();

        //$this->thing_report = array('thing' => $this->thing->thing, 'choices' => null, 'info' => 'This is a reminder.','help' => 'This is probably stuff you want to remember.  Or forget.');

        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'This is a reminder.';
        $this->thing_report['help'] = 'This is probably stuff you want to remember.  Or forget.';

        return $this->thing_report;
    }

	public function chooseResponse($n = null)
    {
		// Choose a response stochastically.

		if ($n == null) {
            $roll = new Roll($this->thing, "roll d20");

$n = 1;
if (isset($roll->sum)) {$n = $roll->sum;}

//            $n = $roll->sum;


        }


		switch ($n) {
			case 1:
				//echo "sendKey";
				return $this->sendReminder();
			case 2:
				return $this->randomComment();
			case 3:
				return $this->sendReminder();
			case 4:
				return $this->sendReminder();
			case 5:
				return $this->sendReminder();
			case 20:
				return $this->sendReminder();
			default:
                return $this->randomComment();
			}
		return $n;
	}

	public function sendReminder()
    {
		$thing_report = new Reminder($this->thing);
		$this->thing->flagGreen();

		return $thing_report;
	}

	public function randomComment()
    {
        $thing_report = new Hey($this->thing);
        $this->thing->flagGreen();
	}
}
?>
