<?php
namespace Nrwtaylor\StackAgentThing;

use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Receipt {

	function __construct(Thing $thing, $agent_input = null) {

		//if ($agent_input == null) {$agent_input = '';}
		$this->agent_input = $agent_input;

		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.
		$this->thing = $thing;
//        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['thing'] = $thing;

        $this->start_time = $this->thing->elapsed_runtime();

		$this->agent_name = 'receipt';
        $this->agent_prefix = 'Agent "Receipt" ';

		// Get some stuff from the stack which will be helpful.
//		$this->web_prefix = $thing->container['stack']['web_prefix'];

		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];



		// Create some short-cuts.
	        $this->uuid = $thing->uuid;
	        $this->to = $thing->to;
	        $this->from = $thing->from;
	        $this->subject = $thing->subject;
		//$this->sqlresponse = null;



		$this->thing->log('<pre> Agent ' . ucfirst($this->agent_name) . '" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("receipt management"=>
						array("learning","communicating"=>
							array("more","less"),"channeling"=>
								array("narrowing","broadening")),
							"receipt start"=>
								array("more"=>"receipt management",
									"less"=>"receipt management"));

		$this->aliases = array("learning"=>array("good job"));

        $this->thing->log('Agent "Receipt" constructed a Thing '. $this->uuid . '', "INFORMATION");
        $this->thing->log( 'Agent "Receipt" received this Thing "' . $this->uuid . '"', "INFORMATION");

		$this->readSubject();

        $this->setReceipt();
        $this->PNG();

        if ($this->agent_input == null) {$this->respond();}


        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;


	}

    function getQuickresponse()
    {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }


    function setReceipt()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("receipt",
            "refreshed_at"),  $this->thing->json->time()
            );

    }

	public function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

        $choices = false;

		$from = $this->from;
		$to = $this->to;

		//echo "from",$from,"to",$to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

$message = "Thank you $from your message to agent '$to' has been accepted by " . $this->short_name .".  Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

$this->makeSMS();



            $this->thing_report['email'] = $message;
            $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
            $this->thing_report['txt'] = $this->sms_message;

            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;




		return $this->thing_report;
	}


    function makeSMS()
    {
        $this->verbosity = 1;

        $this->sms_message = "RECEIPT";

        if ($this->verbosity > 5) {
            //$this->sms_message = "RECEIPT";
            $this->sms_message .= " | thing " . $this->uuid ."";
            $this->sms_message .= " created " . $this->thing->thing->created_at;
            $this->sms_message .= " by " . strtoupper($this->from);
        }

        if ($this->verbosity >=1) {
            $this->sms_message .= " | datagram " . $this->uuid . " received " . $this->thing->thing->created_at. ".";
        }

        //$this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;

        return $this->sms_message;
    }

	public function readSubject()
    {
        $status = true;
        return $status;
    }

    public function PNG()
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
                $codeText = "thing:".$this->uuid;

$agent = new Qr($this->thing, $codeText);
$this->thing_report['png'] = $agent->PNG;

                return $this->thing_report['png'];
                }




}









?>
