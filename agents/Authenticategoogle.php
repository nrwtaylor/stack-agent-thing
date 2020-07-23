<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';


ini_set("allow_url_fopen", 1);

class Authenticategoogle {
	

	public $var = 'hello';


    function __construct(Thing $thing) {


		$this->test= "Development code";


//		$thingy = $thing->thing;
		$this->thing = $thing;

               	$this->api_key = $this->thing->container['api']['google']['API key'];

		$this->client_id  = $this->thing->container['api']['google']['client ID'];
 		$this->client_secret = $this->thing->container['api']['google']['client secret'];



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

	$client = new Google_Client();
	$client->setDeveloperKey($this->api_key);

$client->setAccessType('online'); // default: offline
$client->setApplicationName('Stackr');
$client->setClientId($this->client_id);
$client->setClientSecret($this->client_secret);
//$client->setRedirectUri($scriptUri);
//$client->setDeveloperKey('INSERT HERE'); // API key

exit();


$this->node_list = array("authenticate request"=>array("authenticate verify"=>array("authenticate request")));

		echo '<pre> Agent "Authenticate Google" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Authenticate Google" received this Thing "';echo $this->subject;echo'"</pre>';

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


		$this->thing->account['thing']->Debit(10);




		$this->readSubject();
		$this->respond();


// Err ... making sure the state is saved.
$this->thing->choice->Choose($this->state);


        echo '<pre> Agent "Authenticate" end state is ';
        $this->state = $thing->choice->load('token');
        //echo $this->thing->getState('usermanager');
        echo $this->state;
        echo'"</pre>';



		echo '<pre> Agent "Authenticate" completed</pre>';

		return;

		}


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



	}


	public function readSubject() {

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




                }

		$this->thing->choice->Create('authenticate', $this->node_list, $this->state);




		return false;

	
	}




function authenticateRequest() {

	$this->sendSMS();
	return;

}


	function create() {


                                $ant_pheromone['stack'] = 4;

                                if ((rand(0,5) + 1) <= $ant_pheromone['stack']) {
                                 $this->thing->choice->Create('token', $this->node_list, "authenticate request");

                               } else {
				$this->thing->choice->Create('token', $this->node_list, "authenticate request");

                                }


		$this->thing->flagGreen();

		return;
	}

	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}


}

