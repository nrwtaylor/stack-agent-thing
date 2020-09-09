<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

// devstack Shuffeall, comes from Forgetall, leads to Stack Balance.

class Shuffleall {

	function __construct(Thing $thing)
    {
		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start","opt-in");


		$this->thing->log('Agent "Shuffle All" running on Thing ' .  $this->uuid . '');

		// Kind of pointless because we are going to forget it.  But leave in for now.

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("shuffleall", "refreshed_at") );

        if ($time_string == false) {

            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("shuffleall", "refreshed_at"), $time_string );

        }


        $this->sms_message = "";

		$this->getSubject();

		// And forget this ...
		$this->ShuffleAll();

		$this->setSignals();

		// Not forgeting this Thing.
		$this->thing->Shuffle();

		return;
	}

    function ShuffleAll()
    {

        // Getting memory error from db looking
        // up balance for null
        if ($this->from == "null@stackr.ca") {
            $this->sms_message = "Shuffle All requires an identity.";

            return;
        }

        // devstack paged input

        // Get all users records

        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        $this->total_things = count($things);

        $start_time = time();

        $count = 0;
		shuffle($things);

        $start_time = time();

//        echo count ($this->total_things);

        while (count($things) > 1) {

//			echo "<br>";
//			echo count($things);
            $thing = array_pop($things);

            /* Experiment with interim messaging
			if ( time() - $start_time > 2 ) {

				$this->sms_message .= "Timed out. | ";
				echo "meep";
				break;
				exit();

			}
            */

//			echo $thing['uuid'];echo "<br>";
//			echo $this->uuid;
//			echo "<br>";

			if ($thing['uuid'] != $this->uuid) {

//				echo "no match";
//				echo "-forget thing not implemented";
               	$temp_thing = new Thing($thing['uuid']);
				$temp_thing->Shuffle();

                $count += 1;

			} else {

				//echo "match";

			}
		}

//        echo "<br>" . "complete" . $count;


		$this->sms_message .= "Completed request for this Identity. Shuffled ". $count . " Things.";

		return;
	}

	public function setSignals()
    {
		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen();

 		$this->thing->json->setField("variables");

		$this->sms_message = "SHUFFLE ALL | " . $this->sms_message . " | TEXT FORGET ALL";

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

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

	public function getSubject()
    {
	}

}





?>
