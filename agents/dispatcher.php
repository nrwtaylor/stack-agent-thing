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
//require '../src/translink.php';

//echo "Development<br>";
//echo "Dispatcher says hi<br>";

			// Dispatcher's job is to read the subject and place
			// text in the settings field as to priority.

			// emergency
			// priority
			// routine
			// welfare

			// test is a seperate variable to allow for testing of higher
			// level priorities without action.

			//$this->priority is set at this point.

class Dispatcher {

	// Responds to a query from $from to useragent@stackr.co

	function __construct(Thing $thing) {


		// We need access to all the Thing classes (ie json and db) which 
		// we get by this invocation.
		$thingy = $thing->thing;
		$this->thing = $thing;


		// Playing with these will lead to framework devopment.  Allow for a 
		// space here to develop dispatcher.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

$this->thing->log('Agent "Dispatcher" running on Thing ' .  $this->thing->nuuid . '.');

		// This should be all we need.  At this point to must be
		// sanitized of non-stacker addresses.

		// Run a quick chat and raise an exception.

//		if (strpos($this->to, "@")) {
//			//$this->thing->db->writeField("nom_to", "");
//			//$this->nom_to = "";
//			echo "Wiped";
//			throw new Exception('Nominal information.');
			

			// Wipe field.  This shouldn't have happened.

		//}



		// some magic happers here to make the above happen.
		// and if I seek to optimize this I can see myself messing 
		// around with Thing in the first instance.
		// 
		// Remember magic here - don't mess with the stuff up above.

		//




		// This sounds like a silly variable to need.
		//
		//$this->response_format = "text no images";

		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.

		$this->readSubject();
		$this->response();

		$this->thing->log('Agent "Dispatcher" assigned this Thing ' . $this->priority . ' priority.');
		$this->thing->log('Agent "Dispatcher" completed.');



		


	}




	public function response() {

		// Thing actions

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("dispatcher", "priority"), $this->priority);


		$this->thing->flagRed();

		// Thing email etc.


		$response = null;

	return $response;
	}




	public function readSubject() {

		//if(strpos($this->subject, 'Stack record: ')) {$this->thing->flagRed();}

//echo '<pre> dispatcher.php readSubject() </pre>';

		// If nothing else, just flag it as "routine".
		$this->priority = "routine";


		$cases = array('emergency', 'priority', 'routine', 'welfare');

		$cases = array(
			"emergency"=>array('e/', 'emergency', 'help', 'urgent', 'assistance', 'sos', 'now', 'immediately'),
			"priority" => array('p/', 'priority'),
			"routine" => array('r/', 'routine'),
			"welfare" => array('w/', 'welfare')
			);

		$input = strtolower($this->subject);
		//echo $input;

		$score = array("emergency"=>0, "priority"=>0, "routine"=>0, "welfare"=>0);

		$pieces = explode(" ", strtolower($input));

		foreach ($cases as $case => $keywords) {

			// case = priority routine etc.

			foreach ($pieces as $key=>$piece) {
			
				foreach ($keywords as $discriminator) {
					//echo $piece, $discriminator . "    " . ($piece == $discriminator) ."<br>";
				
					if ($piece == $discriminator) {

// echo '<pre>  db.php Get()'; print_r($score); echo '</pre>';


						$score[$case] = $score[$case] + 1;



					}
				}

			}
		

	}

// 	echo '<pre>  db.php Get()'; print_r($score); echo '</pre>';
	
	// Dispatch case will be the first
		foreach ($cases as $case => $unusedvalue) {

			if ($score[$case] >= 1) {
//				echo "Dispatched as: " .$case.'<br>';
				$this->priority = $case;
				return $case;
			}
		}


	return false;		
	}


}









?>
