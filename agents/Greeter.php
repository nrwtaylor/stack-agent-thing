<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.



//// NOT REALLY EVEN STARTED ON THIS.  Based on dispatch.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Greeter extends Agent {

	// Responds to the first email from a potential new user.
	// The stack has a white-list of emails which have opted into stack.
	// If an email address is not in the white list then 

	function init {

		if (strpos($this->to, "@")) {
			throw new Exception('Nominal information.');
		}

		$this->response_format = "text no images";

		$subject = "Limited beta invitation to Stackr";
		$message = $this->response();

		$email = new Email($this->thing);
		$email->sendGeneric($this->from,'greeter',$subject,$message);
	

    	$this->thing->json->setField("variables");

		$this->thing->json->addVariable(array("greeter"), array(
			'log:{"invitation":' . gmdate("Y-m-d\TH:i:s\Z", time()) . '}'
			));
	
		$json_data = $this->thing->json->json_data;


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
