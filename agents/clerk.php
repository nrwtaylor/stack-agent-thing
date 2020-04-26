<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

ini_set("allow_url_fopen", 1);

class Clerk {
	

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

		$this->api_key = $this->container['api']['clerk'];


		$thingy = $thing->thing;
		$this->thing = $thing;



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

		echo '<pre> Agent "Clerk" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Clerk" received this Thing "';echo $this->subject;echo'"</pre>';


		// Read the subject as passed to this class.
		$this->readSubject();
		$this->respond();




		echo '<pre> Agent "Clerk" completed</pre>';

		return;

		}


	private function respond() {



		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$command = $this->readSubject();

		$to = $this->thing->from;
		$from = "clerk";

		// 
		// Access the Thing's stack account.



	 	// Which it doesn't have yet.  So back to account.php to load up the
		// Thing's account balances.		

		

		switch($command) {
			case 'credit':
				echo '<pre> Agent "Clerk" identified a Credit transaction.</pre>';
				$old_number = $this->thing->account[$this->account_name]->balance['amount'];	


				$this->thing->account[$this->account_name]->Credit($this->amount);
				echo $this->thing->account[$this->account_name]->balance['amount'
];
				break;
			case 'create':
				echo '<pre> Agent "Clerk" identified an Account creation transaction.</pre>';
				$balance = array("amount"=>$this->amount, "attribute"=>$this->attribute, "unit"=>$this->unit);
				$this->thing->newAccount($this->account_name, $balance);
				$old_number = 0;
				break;

			case 'destroy':
				//etc
				break;


			default:
				//echo 'default';
			}
		

		$new_number = $this->thing->account[$this->account_name]->balance['amount'];
		echo "new number :", $new_number;




		$this->message = "Thank you for your request.  The following accounting was done: " .  $old_number ." + ". $this->amount . " = " . $new_number;

		echo $this->message;
		
////		$this->thing->email->sendGeneric($to,$from,$this->subject, $this->message);
//		echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


	public function readSubject() {

		$this->response = null;

		// Look for one number in the subject line.	
		// If there is more than one, don't use any.
		$this->amount = $this->getAmount();


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

	public function getAmount() {
		//$this->subject = "1 2 -3 -4.5,56 90 123.01 -80.01 100,23 -34, 100,000,000";

		//preg_match_all('/((?:[0-9]+,)*[0-9]+(?:\.[0-9]+)?)/', $this->subject, $numbers);
		preg_match_all('/(\+|-)?((?:[0-9]+,)*[0-9]+(?:\.[0-9]+)?)/', $this->subject, $numbers);
		$numbers = $numbers[0]; // Take first element of three part array.

		// http://stackoverflow.com/questions/15814592/how-do-i-include-negative-decimal-numbers-in-this-regular-expression

//	echo '<pre> $numbers: '; print_r($numbers); echo '</pre>';
//	echo '<pre> $count(numbers): '; print_r(count($numbers)); echo '</pre>';

		if (count($numbers) == 1) {$this->amount = $numbers[0];return $numbers[0];}
		if (count($numbers) > 1) {$this->amount = false;return false;}

		return implode(",", $numbers);
		}

	public function scoreCredit() {

		// Score the likelihood this is a request to credit an account.
		$confidence = 0.0;
		$this->response = null;
		
		$keywords = array('credit', 'debit');

		$input = strtolower($this->subject);
		$pieces = explode(" ", strtolower($input));

		foreach  ($keywords as $command) {		
			foreach ($pieces as $key=>$piece) {

				if (strpos(strtolower($piece),$command) !== false) {
					try {
					// is either debit or credit
						$confidence = 0.0;
						$this->action = $pieces[$key];	
						$this->account_name = $pieces[$key+1];					
						if (isset($pieces[$key+1])) {
							$this->amount = $pieces[$key+2];
							if (is_numeric($this->amount)) {
								//echo "numeric";
								$confidence = $confidence + 0.6;
							
							} else {
								$confidence = 0.0;
							
							}
						}

						if (isset($pieces[$key+3])) {
							$this->attribute = $pieces[$key+3];	
							$confidence = $confidence + 0.3;
						}

						if (isset($pieces[$key+4])) {
							$this->unit = $pieces[$key+4];	
							$confidence = $confidence + 0.3;
						}
					}
					catch (Exception $e) {
					
					}
					echo $confidence;
					}

				}
			}
		
		return $confidence;
		
	}

	function scoreCreate() {
		$confidence = 0.0;
		$this->response = null;

		$keywords = array('create', 'make', 'log', 'track', 'new', 'open');

		$input = strtolower($this->subject);

		$pieces = explode(" ", strtolower($input));

		foreach  ($keywords as $command) {
			
			foreach ($pieces as $key=>$piece) {

				
				if (strpos(strtolower($piece),$command) !== false) {
					try {
						// is either debit or credit
						$confidence = 0.0;
						$this->action = $pieces[$key];	
						$confidence = 0.2;					
						$this->account_name = $pieces[$key+1];
						$this->amount = $pieces[$key+2];

						if (is_numeric($this->amount)) {
							//echo "numeric";
							$confidence =0.8;
							}

						$this->attribute = $pieces[$key+3];
						if (isset($pieces[$key+4])) {
							$this->unit = $pieces[$key+4];	
							$confidence = 0.95;
						}
					}
					catch (Exception $e) {

					}

				}
			}

		
		return $confidence;
		}
	}

}




return;

?>

