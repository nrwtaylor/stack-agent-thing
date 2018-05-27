<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);


class Forgetall {

	function __construct(Thing $thing) {
		$this->thing = $thing;

                $this->thing_report['thing'] = $this->thing->thing;


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

//if ($this->thing == false) { //Not sure why sent here?
//	 $this->sms_message = "Received a false Thing."; 
//	$this->setSignals();
//	return;
//}

        	$this->uuid = $thing->uuid;
	        $this->to = $thing->to;
        	$this->from = $thing->from;
        	$this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start");

		echo '<pre> Agent "Forget All" running on Thing ';echo $this->uuid;echo'</pre>';

		// Kind of pointless because we are going to forget it.  But leave in for now.



                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("forgetall", "refreshed_at") );

                if ($time_string == false) {

                        $this->thing->json->setField("variables");
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("forgetall", "refreshed_at"), $time_string );

                }


$this->sms_message = "";


		$this->getSubject();
		//$this->setSignals();

		// And forget this ...
		$this->ForgetAll();

		$this->setSignals();

		// Not forgeting this Thing.
		$this->thing->Forget();

		return;
	}

        function ForgetAll() {

                // Calculate streamed adhoc sample statistics
                // Like calculating stream statistics.
                // Keep track of counts.  And sums.  And squares of sums.
                // And sums of differences of squares.

                // Get all users records
//echo "x" . $this->from . "x";
//exit();  

              $this->thing->db->setUser($this->from);
                $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

                $things = $thingreport['thing'];


                $this->total_things = count($things);


                $start_time = time();


//                $variables = array();
$count = 0;
		shuffle($things);

$start_time = time();

echo count ($this->total_things);

                while (count($things) > 1) {

			echo "<br>";
			echo count($things);
                        $thing = array_pop($things);

//			if ( time() - $start_time > 2 ) {

//				$this->sms_message .= "Timed out. | ";
//				echo "meep";
//				break;
//				exit();

//			}

			echo $thing['uuid'];echo "<br>";
			echo $this->uuid;
			echo "<br>";
//exit();
			if ($thing['uuid'] != $this->uuid) {

//				echo "no match";
//				echo "-forget thing not implemented";
                        	$temp_thing = new Thing($thing['uuid']);
				$temp_thing->Forget();
$count += 1;
			} else {
				echo "match";
//exit();
			}
		}
echo "<br>" . "complete" . $count;
//exit();

		$this->sms_message .= "Completed request for this Identity. Forgot ". $count . " Things.";
//		$this->thing->Forget();
		return;
	}



	public function setSignals() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 


 		$this->thing->json->setField("variables");

		$this->sms_message = "FORGET ALL | " . $this->sms_message . " | TEXT PRIVACY";

		// Will it pass this forward?
		// Must do to report on outcome.
		// devstack could create a null Thing.

		// This would retain an image of the Thing in the response.  This
		// is clearly not the intent of someone requesting FORGET ALL. 
		//$this->thing_report['thing'] = $this->thing->thing;
		// So return false
		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['sms'] = $this->sms_message;

		// While we work on this
		$this->thing_report['email'] = $this->sms_message;

                $message_thing = new Message($this->thing, $this->thing_report);

//                $message_thing = new Message($this->thing, $this->thing_report);


                $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		return $this->thing_report;
	}






	public function getSubject() {
	}


}





?>
