<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;
use Endroid\QrCode\QrCode;

//QR_Code::png('Hello World');

// Recognizes and handles UUIDS.  Does not generate.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Qr
{

	function __construct(Thing $thing, $agent_input = null)
    {
        $this->thing_report['thing'] = $thing;

//        if ($agent_input == null) {$agent_input = '';}
        $this->agent_input = $agent_input;
        $this->agent_name = "qr";
        // Given a "thing".  Instantiate a class to identify and create the
        // most appropriate agent to respond to it.
        $this->thing = $thing;

        //$this->thing_report['thing'] = $this->thing->thing;

		// Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];

		// Create some short-cuts.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		//$this->sqlresponse = null;
        $this->created_at =  strtotime($thing->thing->created_at);

        $this->thing->log('started running on Thing ' . date("Y-m-d H:i:s") . '');
		$this->node_list = array("qr"=>
						array("qr","uuid","snowflake"));

		$this->aliases = array("learning"=>array("good job"));

        if ($this->agent_input == null) {
            $this->quick_response = $this->web_prefix . "" . $this->uuid . "" . "/qr";
        } else {
            $this->quick_response = $this->agent_input;
        }


		$this->readSubject();


        if ($this->agent_input == null) {
		    $this->respond();
        }


        $this->makePNG();


        $this->thing->log('found ' . $this->quick_response);

        //$this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');
        //echo '<pre> Agent "Receipt" completed on Thing ';echo ;echo'</pre>';

        //echo $agent_input;

	}

    function extractQuickresponse($input)
    {
        if (!isset($this->quick_responses)) {
            $this->quick_responses = array();
        }

        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        //preg_match_all($pattern, $input, $m);

        //$arr = $m[0];
        //array_pop($arr);
        $this->quick_responses[] = $input;
        return $this->quick_responses;


    }

    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/qr';

        $this->node_list = array("qr"=>array("qr", "uuid"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "qr");
        $choices = $this->thing->choice->makeLinks('qr');

        $alt_text = "a QR code with a uuid";

        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/qr.png" jpg" 
//                width="100" height="100" 
//                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/qr.txt">';

        $web .= $this->html_image;

        $web .= "</a>";


        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        //$ago = $this->thing->human_time ( $this->created_at );
        //$web .= "Created about ". $ago . " ago.";
        $web.= "<b>QR (Quick Response) Agent</b><br>";
        $web.= "qr is " . $this->quick_response. "<br>";

        $web.= "created at " . strtoupper(date('Y M d D H:m',$this->created_at)). "<br>";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


	public function respond()
    {
		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("qr",
			"received_at"),  $this->thing->json->time()
			);

		$this->thing->flagGreen();

		$from = $this->from;
		$to = $this->to;

		$subject = $this->subject;

		// Now passed by Thing object
		$quick_response = $this->quick_response;
		$sqlresponse = "yes";

        $message = "Thank you $from here is a QR (Quick Response).<p>" . $this->web_prefix . "thing/$quick_response\n$sqlresponse \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $quick_response.'/receipt.png" alt="thing:'.$quick_response.'" height="92" width="92">';

        $this->makeSMS();

//        $this->thing_report['email'] = array('to'=>$from,
//					'from'=>'uuid',
//					'subject'=>$subject,
//					'message'=>$message,
//					'choices'=>$choices);

        $this->thing_report['email'] = $this->thing_report['sms'];

		$this->makePNG();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		//$this->thing_report['thing'] = $this->thing->thing;

        $this->makeWeb();
		return;
	}

	public function readSubject()
    {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.

        //        $message_thing = new Message($this->thing, $this->thing_report);
        //        $thing_report['info'] = $message_thing->thing_report['info'] ;

        // Then look for messages sent to UUIDS
        $this->thing->log('looking for QR in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        if (preg_match($pattern, $this->to)) {
            $this->thing->log('found a QR in address.');
        }
        return;


    }

    function makeSMS()
    {
        $this->sms_message = "QR | ";
        $this->sms_message .= $this->quick_response;
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices ()
    {
        $this->thing->choice->Create("uuid", $this->node_list, "qr");

        $choices = $this->thing->choice->makeLinks("qr");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    public function makePNG()
    {
        if (isset($this->PNG)) {return;}

        if ($this->agent_input == null) {
            $codeText = $this->quick_response;
            //$codeText = $this->web_prefix . "thing/".$this->uuid . "/qr";
        } else {
            $codeText = $this->agent_input;
        }
        if (ob_get_contents()) ob_clean();

        $qrCode = new QrCode($codeText);
var_dump($codeText);
        ob_start();
        echo $qrCode->writeString();
        $image = ob_get_contents();

        ob_clean();
        ob_end_clean();

        $this->PNG_embed = "data:image/png;base64,".base64_encode($image);
        $this->PNG = $image;

        $this->width = 100;
        $alt_text = $this->uuid;

        $html = '<img src="data:image/png;base64,'. base64_encode($image) . '"
                width="' . $this->width .'"  
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/qr.txt">';


        $this->html_image = $html;


        // Can't get this text editor working yet 10 June 2017

        //$textcolor = imagecolorallocate($image, 0, 0, 255);
        // Write the string at the top left
        //imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

        $this->thing_report['png'] = $image;

        //echo $this->thing_report['png']; // for testing.  Want function to be silent.

        return $this->thing_report['png'];
    }
/*
    public function makePNG()
    {
        //if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png");
        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        $this->thing_report['png'] = $agent->image_string;

    }
*/

}


?>
