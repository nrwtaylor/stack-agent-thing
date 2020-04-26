<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';


ini_set("allow_url_fopen", 1);

class Authenticate {
	

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
		//$settings = require '/var/www/html/stackr.ca/src/settings.php';
		//$this->container = new \Slim\Container($settings);

		// create app instance
		

		//$app = new \Slim\App($this->container);
		//$this->container = $app->getContainer();


		


		$this->test= "Development code";


//		$this->container['api'] = function ($c) {
//			$db = $c['settings']['api'];
//			return $db;
//			};

//		$this->api_key = $this->container['api']['translink'];


//		$thingy = $thing->thing;
		$this->thing = $thing;


                // Example
                $this->api_key = $this->thing->container['api']['translink'];



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

	

//$this->sendSMS();
echo "foo";
$this->sendUSshortcode();
echo "bar";
exit();


$this->node_list = array("authenticate request"=>array("authenticate verify"=>array("authenticate request")));

		echo '<pre> Agent "Authenticate" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Authenticate" received this Thing "';echo $this->subject;echo'"</pre>';

		//echo "construct email responser";

		//$this->thing->account->scalar = new Account($this->uuid,

//		$scalar_amount = 0;
//		$this->createAccount($scalar_amount); // Yup


		// Read the subject as passed to this class.

	echo '<pre> Agent "Authenticate" start state is ';
	$this->state = $thing->choice->load('token'); //this might cause problems
	//echo $this->thing->getState('usermanager');
	echo $this->state;
	echo'"</pre>';

//echo "foo";
//$balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
//       		$t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem
//	echo "bar";								// using the thing uuid.
//print_r($t);
//exit();



		$this->thing->account['thing']->Debit(10);




		$this->readSubject();
		$this->respond();


// Err ... making sure the state is saved.
$this->thing->choice->Choose($this->state);

		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.
		
		//return $this->getThing();

        echo '<pre> Agent "Authenticate" end state is ';
        $this->state = $thing->choice->load('token');
        //echo $this->thing->getState('usermanager');
        echo $this->state;
        echo'"</pre>';



		echo '<pre> Agent "Authenticate" completed</pre>';

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
		$from = "authenticate";

		echo "<br>";



		$choices = $this->thing->choice->makeLinks($this->state);
		echo "<br>";
		//echo $html_links;


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Authenticate state: ' . $this->state . '<br>';


		
		$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report = array('thing' => $this->thing->thing, 'choices' => $choices, 'info' => 'This is a hive state engine.','help' => 'Ants.  Lots of ants.');


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


	public function readSubject() {

		//test
//echo "meep";
		//$this->middenwork();

		$this->response = null;

		if ($this->state == null) {
		echo "authenticate detected state null - run subject discriminator";

		

		switch ($this->subject) {
			case "authenticate request":
				//echo "spawn";
				$this->create();
				break;
			case "authenticate verify":
				//$this->kill();
				break;

			default:
			   echo "not found => create()";
				$this->create();
		}


		}


		$this->state = $this->thing->choice->load('authenticate');

		echo "this state is " .$this->state;
		//echo "meep";

		// Will need to develop this to only only valid state changes.

                switch ($this->state) {
                        case "authenticate request":
                                $this->authenticateRequest();

                                break;
                        case "authenticate verify":
                                //$this->kill();
                                break;
              
                 
                        default:
                           echo "not found";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.


		                //if (rand(0,5)<=3) {
               			//         $this->thing->choice->Create('hive', $this->node_list, "inside nest");
                		//} else {
                        	//	$this->thing->choice->Create('hive', $this->node_list, "midden work");
                		//}



                }

		$this->thing->choice->Create('authenticate', $this->node_list, $this->state);




		return false;

	
	}



function sendSMS() {
// API key f0d9c048
// API sectriy eeb905cb8b0704c2

$url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
    [
      'api_key' =>  'f0d9c048',
      'api_secret' => 'eeb905cb8b0704c2',
      'to' => '17787920847',
      'from' => '17784012132',
      'text' => 'Authentication key: ' . rand(100000,999999) 
    ]
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

echo $response;


  //Decode the json object you retrieved when you ran the request.
  $decoded_response = json_decode($response, true);

  error_log('You sent ' . $decoded_response['message-count'] . ' messages.');

  foreach ( $decoded_response['messages'] as $message ) {
      if ($message['status'] == 0) {
          error_log("Success " . $message['message-id']);
      } else {
          error_log("Error {$message['status']} {$message['error-text']}");
      }
  }

return;
}



function sendUSshortcode() {
// API key f0d9c048
// API sectriy eeb905cb8b0704c2

//https://rest.nexmo.com/sc/us/alert/json?api_key={$your_key}&api_secret={$your_secret}&
// to={$to}&key1={$value1}&key2={$value2}

$url = 'https://rest.nexmo.com/sc/alert/json?' . http_build_query(
    [
      'api_key' =>  'f0d9c048',
      'api_secret' => 'eeb905cb8b0704c2',
//      'from' => '96167',
      'to' => '17787920847',
//	'text' => 'test test',
	'message' => 'test',
      'key1' => 'message',
      'key2' => 'world'
      //'message' => 'test value 2: ' . rand(100000,999999) 
    ]
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

echo $response;


  //Decode the json object you retrieved when you ran the request.
  $decoded_response = json_decode($response, true);
echo $decoded_response;

  error_log('You sent ' . $decoded_response['message-count'] . ' messages.');

  foreach ( $decoded_response['messages'] as $message ) {
      if ($message['status'] == 0) {
          error_log("Success " . $message['message-id']);
      } else {
          error_log("Error {$message['status']} {$message['error-text']}");
      }
  }
return;
}


function authenticateRequest() {

	$this->sendSMS();
	return;

}


	function create() {

		//$this->thing = new Thing(null);
		//$this->thing->Create("redpanda.stack@gmail.com", "ant", "spawn");

		//$choice = new Choice($ant_thing->uuid);

//		echo $thing->uuid . "<br>";

		//$node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");

		//$current_node = "inside nest";
		//if (rand(0,5)<=3) {
		//	$this->thing->choice->Create('hive', $this->node_list, "inside nest");
		//} else {
		//	$this->thing->choice->Create('hive', $this->node_list, "midden work");
		//}

                                $ant_pheromone['stack'] = 4;

                                if ((rand(0,5) + 1) <= $ant_pheromone['stack']) {
                                 $this->thing->choice->Create('token', $this->node_list, "authenticate request");

                               } else {
				$this->thing->choice->Create('token', $this->node_list, "authenticate request");

                                }


		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}

	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}



//function arrayFlatten($array) {
//        $flattern = array();
//        foreach ($array as $key => $value){
//            $new_key = array_keys($value);
//            $flattern[] = $value[$new_key[0]];
//        }
//        return $flattern;
//} 

}

?>

