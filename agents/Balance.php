<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

class Balance {

	function __construct(Thing $thing) {
//exit();
		$this->thing = $thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start");

//		echo '<pre> Agent "Satoshi" running on Thing ';echo $this->uuid;echo'</pre>';

		
		$this->getSubject();
		$this->setSignals();



//		echo '<pre> Agent "Satoshi" completed</pre>';

	}

	function getBalance() {

	}

        function stackBalance() {
                // Query stack for matching uuid and nom_from

                echo "WORK ON STACK BALANCE";

echo $this->from;
		$this->thing->db->setUser($this->from);
                $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

		//var_dump($thingreport);

                $things = $thingreport['thing'];

		echo count($things);
//exit();

		$balance = 0;
		$start_time = time();

		foreach ($things as $thing) {


	$temp_thing = new Thing($thing['uuid']);

if ( !isset($temp_thing->account['stack']) ) {$b = 0;
	} else {


		$b = $temp_thing->account['stack']->balance['amount'];

	}
	$balance += $b;

        //echo $thing['uuid'] . $thing['nom_from'] . $b .' ' .$balance . "<br>";



}

$end_time = time();

echo "balance: " . $balance;
echo "time: " . ($end_time-$start_time);

$this->balance = $balance;

//var_dump($things);
//exit();
//
//                if ( ($things == null) or $things == array() ) {return false;}

                // Should have an array... which could be presumptuous.
//                if (!is_array($things)) {return false;}

//                if (!isset($this->from)){return false;}

                // Okay pretty sure we can do this now.
//                $thingreport = $this->db->UUids($account_uuid);

                //$variables = $thingreport['variables'];

                //echo $variables;
                return;
                }


	public function setSignals() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 

		$this->stackBalance();

		// This code should return the pdf when called.
 		$this->thing->json->setField("variables");




		$balance = $this->balance;

		$this->sms_message = "BALANCE | ";
		$this->sms_message .= number_format( $this->balance ) . ' units' ;

		$this->sms_message .= ' | TEXT AGE';

		$this->message = $this->sms_message;


//                if ( is_numeric($this->from) ) {
//                        require_once '/var/www/html/stackr.ca/agents/sms.php';
//
//                        $sms_thing = new Sms($this->thing, $this->sms_message);
//                        $this->thing_report['info'] = 'SMS sent';
//                }

                $this->thing_report['thing'] = $this->thing->thing;
                //$this->thing_report['created_at'] = $this->created_at;
                $this->thing_report['sms'] = $this->sms_message;


		$message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;



		return;
	}

	public function getSubject() {
	}






}


?>
