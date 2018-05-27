<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Jarvis {

	public $var = 'hello';


    function __construct(Thing $thing, $input = null) {

		if ($input == null) {
			$this->requested_agent = "Jarvis.";
		} else {
			$this->requested_agent = $input;

			echo $this->requested_agent;
			

		}


		$this->thing = $thing;
		$this->agent_name = 'hey';
	        $thing_report['thing'] = $this->thing->thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 4; // Retain for at least 4 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


$this->num_hits = 0;

		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log( '<pre> Agent "Jarvis" running on Thing ' . $this->uuid . '</pre>' );
		$this->thing->log( '<pre> Agent "Jarvis" received this Thing "' . $this->subject . '"</pre>');


		$this->thing->log( '<pre> Agent "Jarvis" startJarvis() </pre>' );
                $this->startJarvis();

		//else {$this->group_id = $group_id;}

		$this->readSubject();

		if ($input != 'screen') {
			$this->thing->log( '<pre> Agent "Jarvis" respond() </pre>' );

			$this->thing_report = $this->respond();
		}

		$this->PNG();

	        $this->thing_report['info'] = 'Hey';
        	$this->thing_report['help'] = 'HEY';
        	$this->thing_report['num_hits'] = $this->num_hits;


		$this->thing->log( '<pre> Agent "Jarvis" completed</pre>' );

		return;

		}




        public function startJarvis($type = null) {
		//if ($type == null) {$type = 'alphafour';}


//		if ($this->requested_agent == null) {

		//	$s = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4)), 0, 4);
		//	$this->group_id = $s;
//			$this->requested_agent = "null";
//		}

$litany = array("Hello Ironman.",
"Good morning. It's 7 A.M. The weather in Malibu is 72 degrees with scattered clouds. The surf conditions are fair with waist to shoulder highlines, high tide will be at 10:52 a.m.",
"As always sir, a great pleasure watching you work.",
"Sir, take a deep breath.",
"Working on it, sir. This is a prototype.", 
"Oh, hello sir.",
"Yes, sir.",
"All wrapped up here, sir. Will there be anything else?",
'Sir, received "'. $this->subject. '"');
$key = array_rand($litany);
$value = $litany[$key];

			$this->message = $value;
			$this->sms_message = $value;



	                $this->thing->json->setField("variables");
        	        $names = $this->thing->json->writeVariable( array("jarvis", "requested_agent"), $this->requested_agent );
//exit();
                //if ($time_string == false) {
                        $this->thing->json->setField("variables");
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("jarvis", "refreshed_at"), $time_string );
                //}

//echo $this->group_id;
//exit();


                return $this->message;
        }





// -----------------------

	private function respond() {


		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;

		$from = "jarvis";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');
	        $this->thing_report['choices'] = $choices;


		$this->sms_message = "JARVIS | " . $this->sms_message . " | REPLY HELP";
		$this->thing_report['sms'] = $this->sms_message;

		$this->thing_report['email'] = $this->message;

                $message_thing = new Message($this->thing, $this->thing_report);


                $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		return $this->thing_report;


	}


	public function readSubject() {

		$this->response = null;

		return;
	}
        public function PNG() {
// Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

//I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and tes$
//No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my inluded files...
//So beware of your included files!
//Make sure they are not encoded in UTF or otherwise in UTF without BOM.
//Hope it save someone's time.

//http://php.net/manual/en/function.imagepng.php

//header('Content-Type: text/html');
//echo "Hello World";
//exit();

//header('Content-Type: image/png');
//QRcode::png('PHP QR Code :)');
//exit();
                // here DB request or some processing

		if ($this->requested_agent == null) {
			$this->startBork();
		}

                $codeText = "bork:".$this->requested_agent;

                ob_clean();
                ob_start();

                QRcode::png($codeText,false,QR_ECLEVEL_Q,4); 
                $image = ob_get_contents();

                ob_clean();
// Can't get this text editor working yet 10 June 2017

//$textcolor = imagecolorallocate($image, 0, 0, 255);
// Write the string at the top left
//imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

$this->thing_report['png'] = $image;
//echo $this->thing_report['png']; // for testing.  Want function to be silent.

                return $this->thing_report['png'];
                }


}




?>



