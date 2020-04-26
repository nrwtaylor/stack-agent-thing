<?php

//echo "Watson says hi<br>";

require_once '/var/www/html/stackr.ca/agents/message.php';

class Google {
	

	public $var = 'hello';


    function __construct(Thing $thing) {



		// create container and configure it
		$settings = require '/var/www/html/stackr.ca/src/settings.php';
		$this->container = new \Slim\Container($settings);
		// create app instance
		$app = new \Slim\App($this->container);
		$this->container = $app->getContainer();
		$this->test= "Development code";


		$this->container['api'] = function ($c) {
			$db = $c['settings']['api'];
			return $db;
			};

		$this->api_key = $this->container['api']['watson'];



//		$thingy = $thing->thing;
		$this->thing = $thing;

                $this->thing_report['thing'] = $this->thing->thing;


		$this->retain_for = 24; // Retain for at least 24 hours.

	        $this->uuid = $thing->uuid;
        	$this->to = $thing->to;
        	$this->from = $thing->from;
        	$this->subject = $thing->subject;
		
		$this->sqlresponse = null;

		echo '<pre> Agent "Google" running on Thing ';echo $this->uuid;echo'</pre>';
		echo '<pre> Agent "Google" received this Thing "';echo $this->subject;echo'"</pre>';

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.
		$this->readSubject();
		
		$this->respond();

		echo '<pre> Agent "Google" completed</pre>';


		return;

		}





// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "google";

		
		//echo "foo" .'<br>';				
		// Create a new Thing to keep track of
		// this response.
		//$thing = new Thing(null);
		//$thing->Create($from, $to, $this->subject);



//		$email = new Email($thing);

                $message_thing = new Message($this->thing, $this->thing_report);
                //$thing_report['info'] = 'SMS sent';


		$this->thing_report['info'] = $message_thing->thing_report['info'] ;

	
	//	$message = $this->readSubject();

	
		//$thing_report = array("agent"=>$from, "thing"=>$this->thing);

		return $this->thing_report;


	}



	public function readSubject() {



		//mail("nick@wildnomad.com","watson.php readSubject() run" ,"Test message");
		//echo "Hello";
		$this->response = "Watson says hello";

		$this->sms_message = "GOOGLE | Message forwarded to Google Assistant | REPLY QUESTION";
		$this->message = "Message forwarded to Google Assistant";
		$this->keyword = "google";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;


		
		return $this->response;

	
	}






}




return;
