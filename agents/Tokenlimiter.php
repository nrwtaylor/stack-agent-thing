<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Tokenlimiter {
	

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {
		
		if ($agent_input == null) {$agent_input = "";}
		$this->agent_input = $agent_input;
		$this->thing = $thing;


// Call the TokenLimiter which will then 'on-call' the service you are requesting.

		$this->tokens = array('red','red','blue','red','sms', 'facebook', 'slack', 'email');

// Set default rate at 1 per 15 minutes.
		$this->token_window = 30;




                $this->thing->json->setField("variables");
                $time_string = $this->thing->json->readVariable( array("tokenlimiter", "refreshed_at") );

		if ($time_string == false) {
	                $this->thing->json->setField("variables");
			$time_string = $this->thing->json->time();
        	        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $time_string );
		}

                $this->thing->json->setField("variables");
                $tokens = $this->thing->json->readVariable( array("tokenlimiter", "tokens") );


		if ($tokens == false) {


			$this->initTokens();
		} else {$this->tokens = $tokens;}

		

		$elapsed_time = time() - strtotime($time_string);
//		echo "<pre> Time elapsed since last refresh: " ;echo $elapsed_time;echo "</pre>";

// And so at this point we have a timer model.

		// So created a token_generated_time field.

		if ($elapsed_time > $this->token_window) {
 			$this->refreshTokens();
		}


//                echo "<pre>Available tokens"; print_r($this->tokens); echo "</pre>";

// Does agent input have a clear token request




//$this->agent_input $this->tokens
//		$this->token_request = 'blue';
$this->token_request = $this->agent_input;

 //$this->thing->log( '<pre> meep </pre>' );

		foreach ($this->tokens as $key=>$token) {
			if ($token == $this->token_request) {

//				echo "found";

				unset($this->tokens[$key]);


                        	$this->thing->json->setField("variables");
                        	$this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

                                //callAgent($this->thing->uuid, $token);
                            $c = new Callagent($this->thing);
                            $c->callAgent($this->thing->uuid, $token);

$message = 'Agent "Token Limiter" issued a ' . ucfirst($token) . " Token to Thing " . $this->thing->nuuid . ".";
$this->thing->log( '<pre> ' . $message . '</pre>' );
				$this->thing_report['token'] = $token;

				return;
			}
		}

 		$this->thing_report['token'] = false;

                                //$this->thing->json->setField("variables");
                                //$this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

           //             $this->thing->log("<pre>Token issue" . print_r($this->tokens) . "</pre>");

$this->thing->log( 'Agent "Token Limiter" did not provide a Token.' );

//Agenthandler::callAgent($uuid, $to = null);
//callAgent($thing->uuid, $agent_input);

return;


	//function __construct($arguments) {

//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];
		


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

	

$this->node_list = array("token store"=>array("token create"=>array("token use","token store"),"token use"));

		$this->thing->log( '<pre> Agent "Tokenlimiter" running on Thing ' .  $this->thing->nuuid . '.</pre>'); 
		$this->thing->log( '<pre> Agent "Tokenlimiter" received this Thing "' .  $this->subject . '".</pre>');

		//echo "construct email responser";

		//$this->thing->account->scalar = new Account($this->uuid,

//		$scalar_amount = 0;
//		$this->createAccount($scalar_amount); // Yup


		// Read the subject as passed to this class.

	//echo '<pre> Agent "Tokenlimiter" start state is ';
	$this->state = $thing->choice->load('token'); //this might cause problems
	//echo $this->thing->getState('usermanager');
	//echo $this->state;
	//echo'"</pre>';

$balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
       		$t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem

		$this->thing->account['token']->Credit(1);




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

        //echo '<pre> Agent "Tokenlimiter" end state is ';
        $this->state = $thing->choice->load('token');
        //echo $this->thing->getState('usermanager');
  //      echo $this->state;
    //    echo'"</pre>';



//		echo '<pre> Agent "Tokenlimiter" completed</pre>';

		return;



		}


    function initTokens() 
    {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $this->thing->json->time() );

        $this->tokens = array('red','red','blue','red','orange','orange','sms','facebook','slack','email','satoshi','satoshi','microsoft');

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

//                        echo "<pre>Tokens refreshed" ; print_r($this->tokens); echo "</pre>";

                return;
    }


    function refreshTokens()
    {

	                $this->thing->json->setField("variables");
                        $this->thing->json->writeVariable( array("tokenlimiter", "refreshed_at"), $this->thing->json->time() );

                        $this->tokens = array('red','red','blue','red','orange','orange','satoshi');

                        $this->thing->json->setField("variables");
                        $this->thing->json->writeVariable( array("tokenlimiter", "tokens"), $this->tokens );

//                        echo "<pre>Tokens refreshed" ; print_r($this->tokens); echo "</pre>";

		return;
}

	function generateToken() {

	}




// -----------------------

	private function respond() {

		// Thing actions


		$this->thing->flagGreen();


		// Generate email response.

		$to = $this->thing->from;
		$from = "token";

//		echo "<br>";



		$choices = $this->thing->choice->makeLinks($this->state);
//		echo "<br>";
		//echo $html_links;


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Hive state: ' . $this->state . '<br>';

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
		//echo "tokenlimiter detected state null - run subject discriminator";

		

		switch ($this->subject) {
			case "token create":
				//echo "spawn";
				$this->create();
				break;
			case "token store":
				//$this->kill();
				break;
			case "token use":
				$this->thing->choice->Choose("token use");

				break;
			default:
		//	   echo "not found => spawn()";
				$this->create();
		}


		}


		$this->state = $this->thing->choice->load('token');

		//echo "this state is " .$this->state;
		//echo "meep";

		// Will need to develop this to only only valid state changes.

                switch ($this->state) {
                        case "token create":
                                //echo "spawn";
                                //$this->spawn();
                                break;
                        case "token store":
                                //$this->kill();
                                break;
                        case "token use":
                                //$this->thing->choice->Choose("foraging");

                                break;
                        case "inside nest":
                                //$this->thing->choice->Choose("in nest");
                                break;
                        case "nest maintenance":
                                //$this->thing->choice->Choose("nest maintenance");
                                break;
                        case "patrolling":
                                //$this->thing->choice->Choose("patrolling");
                                break;
                        case "midden work":
                                //$this->thing->choice->Choose("midden work");
                                $this->middenwork();

                                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                                // Can green reflag red?  Think about reset conditions.

                                break;
                        default:
                           //echo "not found";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.


		                //if (rand(0,5)<=3) {
               			//         $this->thing->choice->Create('hive', $this->node_list, "inside nest");
                		//} else {
                        	//	$this->thing->choice->Create('hive', $this->node_list, "midden work");
                		//}



                }

		$this->thing->choice->Create('token', $this->node_list, $this->state);




		return false;

	}

    function createToken()
    {

    }


	function create()
    {
        $ant_pheromone['stack'] = 4;

        if ((rand(0,5) + 1) <= $ant_pheromone['stack']) {
            $this->thing->choice->Create('token', $this->node_list, "inside nest");
        } else {
		    $this->thing->choice->Create('token', $this->node_list, "midden work");
        }

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
