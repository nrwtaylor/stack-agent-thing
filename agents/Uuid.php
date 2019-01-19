<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;
//use Endroid\QrCode\QrCode;

//QR_Code::png('Hello World');

// Recognizes and handles UUIDS.  Does not generate.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Uuid
{

	function __construct(Thing $thing, $agent_input = null)
    {
        $this->thing_report['thing'] = $thing;

        if ($agent_input == null) {$agent_input = '';}
        $this->agent_input = $agent_input;
        $this->agent_name = "uuid";
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

        $this->thing->log('Agent "Uuid" started running on Thing ' . date("Y-m-d H:i:s") . '');
		$this->node_list = array("uuid"=>
						array("uuid","snowflake"));

		$this->aliases = array("learning"=>array("good job"));

		$this->readSubject();

        if ($this->agent_input == null) {
		    $this->respond();
        }

        $this->makePNG();

        $this->thing->log('Agent "Uuid" found ' . $this->uuid);

        //$this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');
        //echo '<pre> Agent "Receipt" completed on Thing ';echo ;echo'</pre>';

	}

    function getQuickresponse()
    {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }


    function extractUuids($input)
    {
        if (!isset($this->uuids)) {
            $this->uuids = array();
        }

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->uuids = $arr;
        return $arr;


    }

    function extractUuid($input)
    {
        $uuids = $this->extractUuids($input);
        if (!(is_array($uuids))) {return true;}

        if ((is_array($uuids)) and (count($uuids) == 1)) {
            $this->uuid = $uuids[0];
            $this->thing->log('Agent "Uuid" found a uuid (' . $this->uuid . ') in the text.');
            return $this->uuid;
        }

        if  ((is_array($uuids)) and (count($uuids) == 0)){return false;}
        if  ((is_array($uuids)) and (count($uuids) > 1)) {return true;}

        return true;
    }



    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = array("uuid"=>array("uuid", "snowflake"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "uuid");
        $choices = $this->thing->choice->makeLinks('uuid');

        $alt_text = "a QR code with a uuid";

        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/uuid.png" jpg" 
                width="100" height="100" 
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/uuid.txt">';

        $web .= "</a>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        //$ago = $this->thing->human_time ( $this->created_at );
        //$web .= "Created about ". $ago . " ago.";
        //$web.= "<b>UUID Agent</b><br>";
        //$web.= "uuid is " . $this->uuid. "<br>";

        $web.= "CREATED AT " . strtoupper(date('Y M d D H:m',$this->created_at)). "<br>";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


	public function respond()
    {
		// Thing actions

		$this->thing->json->setField("settings");
		$this->thing->json->writeVariable(array("uuid",
			"received_at"),  $this->thing->json->time()
			);

		$this->thing->flagGreen();

		$from = $this->from;
		$to = $this->to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

        $message = "Thank you $from here is a UUID.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

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

	public function readSubject() {

        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        
        //        $message_thing = new Message($this->thing, $this->thing_report);
        //        $thing_report['info'] = $message_thing->thing_report['info'] ;

        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "UUID" looking for UUID in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');

//        $this->response = "Made a snowflake. Which will melt.";

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
//echo $this->to;
//        preg_match_all($pattern, $this->to, $m);
        if (preg_match($pattern, $this->to)) {
        $this->thing->log('Agent "UUID" found a  UUID in address.');


//        $arr = $m[0];
//        $this->uuids = $arr;
//var_dump($arr);

            //$uuid_thing = new Receipt($this->thing);
            //$this->thing_report['info'] = $receipt_thing->thing_report['info'];

            return;


        }




		$status = true;
	return $status;		
	}

    function makeSMS() 
    {
        $this->sms_message = "UUID | ";
        $this->sms_message .= $this->uuid;
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices ()
    {
        $this->thing->choice->Create("uuid", $this->node_list, "uuid");

        $choices = $this->thing->choice->makeLinks("uuid");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }



    public function makePNG()
    {
        if (isset($this->PNG)) {return;}



        //if (headers_sent()) {return;}  
      // Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

        $codeText = $this->web_prefix . "thing/".$this->uuid;

        $agent = new Qr($this->thing, $codeText);
      //  $this->thing_report['png'] = $agent->PNG;

     //   return $this->thing_report['png'];

        $image = $agent->PNG;

        $this->PNG_embed = "data:image/png;base64,".base64_encode($image);
        $this->PNG = $image;

        $this->thing_report['png'] = $image;

        return $this->thing_report['png'];

    }
}
?>
