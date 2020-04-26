<?php
// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.



//// NOT REALLY EVEN STARTED ON THIS.  Based on dispatch.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';
//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';


require '../src/email.php';

class Greeter {

	// Responds to the first email from a potential new user.
	// The stack has a white-list of emails which have opted into stack.
	// If an email address is not in the white list then 

	function __construct(Thing $thing) {
		//echo "Agent__construct";
		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.

		// We need access to all the Thing classes (ie json and db) which 
		// we get by this invocation.
		$thingy = $thing->thing;
		$this->thing = $thing;


	//echo '<pre> dispatcher.php $thing: '; print_r($thing); echo '</pre>';

		//var_dump($thingy);


		// Playing with these will lead to framework devopment.  Allow for a 
		// space here to develop dispatcher.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		// This should be all we need.  At this point to must be
		// sanitized of non-stacker addresses.

		// Run a quick check and raise an exception.

		if (strpos($this->to, "@")) {
			throw new Exception('Nominal information.');
		}



		// some magic happers here to make the above happen.
		// and if I seek to optimize this I can see myself messing 
		// around with Thing in the first instance.
		// 
		// Remember magic here - don't mess with the stuff up above.




		if ($this->readSubject()) {
			}

		$this->sqlresponse = null;


		// This sounds like a silly variable to need.
		//
		$this->response_format = "text no images";

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.
		$subject = "Limited beta invitation to Stackr";
		$message = $this->response();

		$email = new Email($this->thing);
		$email->sendGeneric($this->from,'greeter',$subject,$message);
	


		$this->thing->json->setField("variables");

		$this->thing->json->addVariable(array("greeter"), array(
			'log:{"invitation":' . gmdate("Y-m-d\TH:i:s\Z", time()) . '}'
			));
	
		$json_data = $this->thing->json->json_data;

		//$t = $this->thing->json->getVariable(array("dispatcher"));



		// We are going to be looking
		//$variable = 'dispatcher:{"response_at":time()}';

		if ($json_data == null) {
			// No text in field.
			} else {
			// Text in the field
			// So extract existing setting
			}





	}




	public function response() {
		$stackr_url = 'https://stackr.co';

		$response = "We recently received an e-mail from this email address.
					If you wish to join Stackr's limited beta, please click
					on the link below to confirm your identity.\r\n";
		$response .= $stackr_url . '/thing/' . $this->uuid . '/opt-in';

	return $response;
		}


	public function readSubject() {
		// No real reason to read the subject.

	return true;		
	}


}









?>
