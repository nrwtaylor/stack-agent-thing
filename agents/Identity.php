<?php
namespace Nrwtaylor\StackAgentThing;

//echo '<pre> Agent "Receipt" started running on Thing ';echo date("Y-m-d H:i:s");echo'</pre>';

// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Identity {

	function __construct(Thing $thing, $agent_input = null) {
//QRcode::png('PHP QR Code :)');
//echo "meep";
//exit(); 



		if ($agent_input == null) {$agent_input = '';}
		$this->agent_input = $agent_input;

			// Given a "thing".  Instantiate a class to identify and create the
			// most appropriate agent to respond to it.
			$this->thing = $thing;

$this->thing_report['thing'] = $this->thing->thing;

		// Get some stuff from the stack which will be helpful.
		$this->web_prefix = $thing->container['stack']['web_prefix'];
		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];


		// Create some short-cuts.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;



        $this->thing->log('<pre> Agent "Uuid" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("identity"=>
						array("who am i"));

		$this->aliases = array("learning"=>array("good job"));

		$this->readSubject();

        if ($this->agent_input == null) {
		    $this->respond();
        }


        $this->thing->log('Agent "Identity" found ' . $this->uuid);

        //$this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');

	}

    function extractUuids($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        if (!isset($this->uuids)) {
            $this->uuids = array();
        }
        
        $agent = new Uuid($this->thing, "uuid");
        if (isset($agent->uuids)) {
            $this->uuids = $agent->uuids;
        } else {
            $this->uuids = null;
        }
        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        //preg_match_all($pattern, $input, $m);

        //$arr = $m[0];
        //array_pop($arr);
        //$this->uuids = $arr;
        return $this->uuids;


    }


	public function respond() {



		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("uuid",
			"received_at"),  $this->thing->json->time()
			);


		$this->thing->flagGreen();


		// What receipts states are there.
		// "Hi.  I'm receipt management.  How can I help you."

		// figure out how set do aliases fluidly "good job"="learning".
		// For now.

		
		foreach ($this->aliases as $alias) {

			// Find out if the array test is in the aliase "database"?  But
			// want to avoid database calls.  They clearly have a real world
			// cost.  We are setting the paramenters for what 100 <unit> costs.
			// Because triggering the db connections issue has to have real 
			// world consequences.

		}



		$this->thing->choice->Create('uuid', $this->node_list, "identity");

		$choices = $this->thing->choice->makeLinks('identity');
//		$html_button_set = $links['button'];



		$from = $this->from;
		$to = $this->to;

		//echo "from",$from,"to",$to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

$message = "Thank you $from here is a UUID.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';



$this->sms_message = "IDENTITY | uuid ";
$this->sms_message .= $this->uuid;
$this->sms_message .= ' | TEXT ?';

/*
                if ( is_numeric($this->from) ) {
                        require_once '/var/www/html/stackr.ca/agents/sms.php';

                        $this->readSubject();

                        $sms_thing = new Sms($this->thing, $this->sms_message);
                        $thing_report['info'] = 'SMS sent';

                //return $thing_report;
                }




if ( $this->thing->account['thing']->balance['amount'] < 0 ) {
	// Sufficiient balance to send an email
	$this->thing->email->sendGeneric($from, "uuid", $subject, $message, $choices);
	$this->thing->account['thing']->Credit(100);
}

*/

$this->thing_report['sms'] = $this->sms_message;

$this->thing_report['email'] = array('to'=>$from,
					'from'=>'uuid',
					'subject'=>$subject,
					'message'=>$message,
					'choices'=>$choices);

		$this->PNG();

                $message_thing = new Message($this->thing, $this->thing_report);
                $thing_report['info'] = $message_thing->thing_report['info'] ;

		$this->thing_report['thing'] = $this->thing->thing;

		return;
	}



	public function readSubject() {

        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        
        //        $message_thing = new Message($this->thing, $this->thing_report);
        //        $thing_report['info'] = $message_thing->thing_report['info'] ;

        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "UUID" looking for UUID in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');


//        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
//        if (preg_match($pattern, $this->to)) {
        $this->extractUuids();

        $this->thing->log('Agent "UUID" found a  UUID in address.');


//        $arr = $m[0];
//        $this->uuids = $arr;
//var_dump($arr);
//            $uuid_thing = new Receipt($this->thing);
//            $this->thing_report['info'] = $receipt_thing->thing_report['info'];
            return;


        }




//		$status = true;
//	return $status;		
//	}

        public function PNG() {
// Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

//I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and testing, including re-installing apache on my computer a couple of times, I traced the problem to an included file.
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
                $codeText = "thing:".$this->uuid;

		ob_clean();

                ob_start();



                QRcode::png($codeText,false,QR_ECLEVEL_Q,4); 

                $image = ob_get_contents();

//header('Content-Type: image/png');
//echo $image;
//exit();

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
