<?php
// Uniqueness.  Is valuable.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Number {

	function __construct(Thing $thing, $agent_input = null)
    {
		if ($agent_input == null) {$agent_input = '';}
		$this->agent_input = $agent_input;
        $this->agent_name = "number";


		// Given a "thing".  Instantiate a class to identify and create the
        // most appropriate agent to respond to it.
		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;

		// Get some stuff from the stack which will be helpful.
        //$this->web_prefix = $GLOBALS['web_prefix'];
        $this->web_prefix = $thing->container['stack']['web_prefix'];


		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];

		// Create some short-cuts.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

//$this->subject = "number ". "please 234,000 1,000,234 find (.3) the -1 20 503 numbers (34) 12.4 in here 12 if you can 12 / 4 = 100000 and some currencies perhaps $6 $23.90 but not $1.000, and in french €5.67";
//$this->test_count = 15; // I think
$this->test_count = null;
// a french test string "or 5€67 or 66,50 £ or 66,50£ or 20 000 $ 99 999 but more rarely 99.999 or 99.999.999 but how about - 12 432,20";

        $this->thing->log('<pre> Agent "Number" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("number"=>
						array("number"));

		$this->aliases = array("learning"=>array("good job"));

		$this->readSubject();

        $this->recognize_french = true; // Flag error


        if ($this->agent_input == null) {
		    $this->respond();
        }

        $this->thing->log('Agent "Number" found ' . $this->uuid);

        // Way to output test information to web page as a thing call
        // $this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');
	}

    function extractNumbers($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }
        // https://www.regular-expressions.info/floatingpoint.html
        // Life goals regex that does this

        if (!isset($this->numbers)) {
            $this->numbers = array();
        }

        $pieces = explode(" ",$input);
        $this->numbers = [];
        foreach ($pieces as $key=>$piece) {

            if (is_numeric($piece)) {
                $this->numbers[] = $piece;
                continue;
            }

            // Treat () as accounting format number
            // Rare to see this in use.
/*
    if (is_numeric(substr($piece,0,-1))) {
            $this->numbers[] = substr($piece,0,-1);
            continue;
    }

    if (is_numeric(substr($piece,-1,1))) {
            $this->numbers[] = substr($piece,-1,1);
            continue;
    }
*/


            if (is_numeric(substr($piece,1,-1))) {
                if ((substr($piece,0,1) == "(") and (substr($piece,-1,1) == ")")) {
                    $this->numbers[] = -1 * substr($piece,1,-1);
                    continue;
                }

                $this->numbers[] = substr($piece,1,-1);
                continue;
            }

            if (is_numeric(str_replace(",", "", $piece))) {
                $this->numbers[] = str_replace(",","",$piece);
                continue;
            }

            // preg_match_all('!\d+!', $piece, $matches);
            preg_match_all('/([\d]+)/',  $piece, $matches);

            foreach ($matches[0] as $key=>$match){
                $this->numbers[] = $match;
            }

        }

        return $this->numbers;
    }

    function extractNumber()
    {
        $this->number = false; // No numbers.
        if (!isset($this->numbers)) {$this->extractNumbers();}

        if (isset($this->numbers[0])) {
            $this->number = $this->numbers[0];
        }

    }


	public function respond()
    {
		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("number",
			"received_at"),  $this->thing->json->time()
			);

		$this->thing->flagGreen();

		$from = $this->from;
		$to = $this->to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

        //$message = "Thank you here is a Number.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
        //$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

        $this->makeSMS();

        //$this->thing_report['email'] = array('to'=>$from,
		//			'from'=>'uuid',
		//			'subject'=>$subject,
		//			'message'=>$message,
		//			'choices'=>$choices);

		//$this->makePNG();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

		$this->thing_report['thing'] = $this->thing->thing;

        $this->thing_report['help'] = "This extracts numbers from the datagram.";

		return;
	}

	public function readSubject()
    {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        if ($this->agent_input == null) {
            $input = $this->subject;
        }

        if ($this->agent_input == "number") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }
        //} elseif (explode(" ",$this->agent_input)[0] == "number") {
        //    $input = $this->agent_input;
        //}

            $this->extractNumbers($input);
        $this->extractNumber();

//var_dump($input);
        // Then look for messages sent to UUIDS
        //$this->thing->log('Agent "Number" looking for UUID in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');

        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        //if (preg_match($pattern, $this->to)) {
        //    $this->thing->log('Agent "Number" found a Number in address.');
        //    return;
        //}

		$status = true;

    	return $status;
	}

    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = array("number"=>array("number", "thing"));

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->subject . "<br>";

        if (!isset($this->numbers[0])) {
            $web .= "No numbers found<br>";
        } else {
            $web .= "First number is ". $this->numbers[0] . "<br>";
            $web .= "Extracted numbers are:<br>";
        }
        foreach ($this->numbers as $key=>$number) {
            $web .= $number . "<br>";
        }

        if ($this->recognize_french == true) {
            if (count($this->numbers) == $this->test_count) {
//https://french.kwiziq.com/revision/grammar/how-to-write-decimal-numbers-in-french
                $web .= "Found all the numbers.  Excluding the french format.";
            }
        }

     //   $web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';
        

        //$web .= "<a href='"  . $link . "'>" . $link . "</a>";
        //$web .= "<br>";
        //$link = "https://en.wikipedia.org/wiki/Universally_unique_identifier";        
        //$web .= "<a href='"  . $link . "'>" . $link . "</a>";

        $web .= "<br>";

//        $web .= $this->help . "<br>";

        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {

        if (!isset($this->numbers)) {
            $this->extractNumbers();
        }

        $this->sms_message = "NUMBER | ";
        foreach ($this->numbers as $key=>$number) {
            $this->sms_message .= $number . " | ";
        }

        $this->sms_message .= 'devstack';

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices ()
    {
        $this->thing->choice->Create("number", $this->node_list, "number");

        $choices = $this->thing->choice->makeLinks("number");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }



    public function makePNG()
    {
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
        $text = "thing:".$this->numbers[0];

		ob_clean();

        ob_start();

        QRcode::png($text,false,QR_ECLEVEL_Q,4);

        $image = ob_get_contents();

        //header('Content-Type: image/png');
        //echo $image;
        //exit();

		ob_clean();

        $this->thing_report['png'] = $image;

        //echo $this->thing_report['png']; // for testing.  Want function to be silent.

    return $this->thing_report['png'];
    }

}

?>
