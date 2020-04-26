<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

ini_set("allow_url_fopen", 1);

class Abc {
	

	public $var = 'hello';


    function __construct(Thing $thing) {

		$this->thing = $thing;
		$this->agent_name = 'abc';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->web_prefix = $this->thing->container['stack']['web_prefix'];


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

	

$this->node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");	

		echo '<pre> Agent "Abc" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Abc" received this Thing "';echo $this->subject;echo'"</pre>';

		//echo "construct email responser";

		//$this->thing->account->scalar = new Account($this->uuid,

//		$scalar_amount = 0;
//		$this->createAccount($scalar_amount); // Yup


		// Read the subject as passed to this class.
		$this->readSubject();
		$this->respond();





		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.
		
		//return $this->getThing();

		echo '<pre> Agent "Abc" completed</pre>';

		return;

		}




//	function createAccount(String $account_name, $amount) {

//		$scalar_account = new Account($this->uuid, 'scalar', $amount, "happiness", "Things forgotten"); // Yup.
//		$this->thing->scalar = $scalar_account;
//		return;
//	}


// -----------------------

	private function respond() {














		// Thing actions


		$this->thing->flagGreen();


		// Generate email response.

		$to = $this->thing->from;
		$from = "abc";





		$choices = $this->thing->choice->makeLinks();
		echo "<br>";
		//echo $html_links;


		$test_message = $choices['url'];

//		$this->message = "Thank you for your request.  The following accounting was done: " .  $old_number ." + ". $this->amount . " = " . $new_number;
		echo "foo";
		echo $test_message;
		echo "bar";
		echo $choices['button'];
		
		$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);
		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


	public function readSubject() {

		$this->response = null;
		switch ($this->subject) {
			case "spawn":
				$this->spawn();
				break;
			case "kill":
				$this->kill();
				break;
			case "a":
				$this->thing->choice->Choose("a");

				break;
			case "b":
				$this->thing->choice->Choose("b");
				break;
			case "c":
				$this->thing->choice->Choose("c");
				break;
			
			default:
			   echo "not found";
		}

		return false;

	
	}




	function spawn() {

		$this->thing = new Thing(null);
		$this->thing->Create("nick@wildnomad.com", "ant", "Choice: in nest");

		//$choice = new Choice($ant_thing->uuid);

//		echo $thing->uuid . "<br>";

		$node_list = array("a"=>array("b"=>array("c"=>array("a"))));

		$current_node = "b";

		$this->thing->choice->Create($node_list, "spawn");


		$current_node = "a";



switch (rand(0,2)) {
    case 0:
        $this->thing->choice->Create($node_list, "a");
        break;
    case 1:
        $this->thing->choice->Create($node_list, "b");
        break;
    case 2:
        $this->thing->choice->Create($node_list, "c");
        break;
    default:
		$this->thing->choice->Create($node_list, null);
       //echo "i is not equal to 0, 1 or 2";
}



//		$this->thing->choice->Choose("b");
		$this->thing->flagGreen();

		return;
	}

	function kill() {
		return $this->thing->Forget();
	}


}

?>

