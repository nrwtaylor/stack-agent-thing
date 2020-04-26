<?php
// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';
//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

//require '../src/watson.php';
//require_once '../src/stackdb.php';
require_once '/var/www/html/stackr.ca/agents/watson.php';



class Notstack {

	function __construct(Thing $thing) {
		//echo "Receipt called";



		$this->thing = $thing;
		$this->agent_name = 'notstack';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;
echo $this->from;
		$this->aliases = array('default', 'ex-stack');
		$this->node_list = array("not stack"=>array("new user", "watson"));

		echo '<pre> Agent "Notstack" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Notstack" received this Thing "';echo $this->subject;echo'"</pre>';


		// If readSubject is true then it has been responded to.
		// Forget thing.

		// Very much assuming this is addressed to 'newuser'


		$this->previous_state = $this->thing->getState('usermanager');

		echo '<pre> Agent "Notstack" determined the current usermanager state: ';echo $this->previous_state;echo'</pre>';


		$this->readSubject();
		$thing_report = $this->respond();

		$this->thing_report = $thing_report;

		echo '<pre> Agent "Notstack" completed</pre>';
		return;
	}

	public function respond() {

		// Thing actions

		// New user is triggered when there is no nom_from in the db.
		// If this is the case, then Stackr should send out a response
		// which explains what stackr is and asks either
		// for a reply to the email, or to send an email to opt-in@stackr.co.

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("notstack","receipt",
			"received_at"),  date("Y-m-d H:i:s")
			);

		$this->thing->flagGreen();

		// Get the current user-state.

		switch ($this->previous_state) {
			case 'opt-out':

				$newuser_thing = new Newuser($this->thing);
$thing_report = $newuser_thing->thing_report;

				break;
			case 'opt-in':

				// Opted-in user has sent a message to stack.
				// Call Watson.

				$watson_thing = new Watson($this->thing);
$thing_report = $watson_thing->thing_report;


				break;
			case 'new user':

				// Ignore repeated attempts.

				
				break;
			case null;

				// See if an existing Opt-in Thing for this user exists.

				$newuser_thing = new Newuser($this->thing);
$thing_report = $newuser_thing->thing_report;
				break;
			default:
				$watson_thing = new Newuser($this->thing);

				$thing_report = $watson_thing->thing_report;
			}



		$this->thing->flagGreen();


		return $thing_report;
	}



	public function readSubject() {
		return;

	}


}


?>
