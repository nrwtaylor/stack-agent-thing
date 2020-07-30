<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Chooser {
	

	public $var = 'hello';


    function __construct(Thing $thing) {
	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];




		// create container and configure it
		$settings = require '../src/settings.php';
		$this->container = new \Slim\Container($settings);
		// create app instance
		$app = new \Slim\App($this->container);
		$this->container = $app->getContainer();
		$this->test= "Development code";


		$this->container['api'] = function ($c) {
			$db = $c['settings']['api'];
			return $db;
			};

		$this->api_key = $this->container['api']['translink'];


		$thingy = $thing->thing;
		$this->thing = $thing;



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

		

		echo '<pre> Agent "Chooser" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Chooser" received this Thing "';echo $this->subject;echo'"</pre>';

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

		echo '<pre> Agent "Clerk" completed</pre>';

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
		$from = "chooser";

//echo '<pre> $this->thing ';print_r($this->thing->account['scalar']->balance);echo'</pre>';

		$this->description = 'scalar';

		$old_number = $this->thing->account[$this->description]->balance['amount'];
		echo "old number :", $old_number;

		switch($this->readSubject()) {
			case 'credit':	
				echo '<pre> Agent "Chooser" identified a Choice.</pre>';
				//echo "meep";
				//$this->thing->account[$this->description]->Credit($this->amount);
				break;
			case 'create':
				echo '<pre> Agent "Chooser" identified an Choice creation transaction.</pre>';

				//$this->thing->account[$this->description] = new Account($this->uuid, $this->description);
				//$this->thing->account[$this->description]->Create($this->amount, $this->attribute, $this->units);

				break;

			case 'destroy':
				//etc
				break;


			default:
				//echo 'default';
			}
		

		$new_number = $this->thing->account[$this->description]->balance['amount'];
		echo "new number :", $new_number;




//		$this->message = "Thank you for your request.  The following accounting was done: " .  $old_number ." + ". $this->amount . " = " . $new_number;

		echo $this->message;
		
//		$this->thing->email->sendGeneric($to,$from,$this->subject, $this->message);
//		echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


	public function readSubject() {

		$this->response = null;

		// search for a credit debit instruction
		//echo $this->scoreCredit();

		// search for an account creation instruction
		//echo $this->scoreCreate();

		if ($this->scoreCredit() > $this->scoreCreate()) {
			// Likely subject is a Credit instruction
			return 'credit';
		} else {
			return 'create';
//			return $this->thing->scalar->Create($this->amount);
		}

		return false;

	
	}



	public function choiceSelect() {
		$confidence = 0.0;
		$this->response = null;
	
		
		return $confidence;
		
	}

	function choiceCreate() {

		
		return $confidence;
	
	}

}




return;

?>

