<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Channel
{
	function __construct(Thing $thing, $agent_input = null)
    {

        //echo "agent_input is " . $agent_input;

        $this->start_time = microtime(true);

		if ($agent_input == null) {$agent_input = '';}
		$this->agent_input = $agent_input;
        $this->agent_prefix = 'Agent "' .ucwords($this->agent_input). '" ';
			// Given a "thing".  Instantiate a class to identify and create the
			// most appropriate agent to respond to it.

        $this->thing = $thing;
        $this->thing_report['thing'] = $thing;

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



        $this->thing->log('<pre> Agent "Channel" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("channel"=>array("cue primary channel"));

		//$this->aliases = array("learning"=>array("good job"));

//		echo '<pre> Agent "Receipt" constructed a Thing ';echo $this->uuid;echo'</pre>';
//		echo '<pre> Agent "Receipt" received this Thing "';echo $this->subject;echo'"</pre>';

        if ($agent_input == "channel") {
            $this->thing->json->setField("variables");
            //$this->roll = strtolower($this->thing->json->readVariable( array("roll", "roll") ));
            $this->channel_name = $this->thing->json->readVariable( array("channel", "name") );
        } elseif ($agent_input != null) {
            $this->channel_name = $agent_input;
        }

        

        $this->readFrom();
		$this->readSubject();

        $this->set();
		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . $milliseconds . 'ms.' );

//$this->thing->test(date("Y-m-d H:i:s"),'receipt','completed');
//echo '<pre> Agent "Receipt" completed on Thing ';echo ;echo'</pre>';

	}

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("channel",
            "refreshed_at"),  $this->thing->json->time()
            );
        $this->thing->json->writeVariable(array("channel",
            "name"),  $this->channel_name
            );

    }

    public function get()
    {
        $this->thing->json->setField("variables");
        //$this->refreshed_at = strtolower($this->thing->json->readVariable( array("roll", "roll") ));
        $this->channel_name = $this->thing->json->readVariable( array("channel", "name") );
    }

	public function respond() {


        $this->blankX();

		// Thing actions
/*
		$this->thing->json->setField("variables");

		$this->thing->json->writeVariable(array("channel",
			"received_at"),  $this->thing->json->time()
			);

        $this->thing->json->writeVariable(array("channel",
            "refreshed_at"),  $this->thing->json->time()
            );


        $this->thing->json->writeVariable(array("channel",
            "channel"),  $this->agent_input
            );
*/


		$this->thing->flagGreen();


		// What receipts states are there.
		// "Hi.  I'm receipt management.  How can I help you."

		// figure out how set do aliases fluidly "good job"="learning".
		// For now.

/*		
		foreach ($this->aliases as $alias) {

			// Find out if the array test is in the aliase "database"?  But
			// want to avoid database calls.  They clearly have a real world
			// cost.  We are setting the paramenters for what 100 <unit> costs.
			// Because triggering the db connections issue has to have real 
			// world consequences.

		}
*/
		//$this->thing->choice->Create('channel', $this->node_list, "start");

		//$choices = $this->thing->choice->makeLinks('start');
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





    // Allow for channel specific injections.
    switch($this->channel_name) {
        case null:
            break;
        case 'email':
            break;
        case 'sms':
            break;
        case '3':
            break;
        case '4':
            break;
        default:
            break;
    }


/*

if ( $this->thing->account['thing']->balance['amount'] < 0 ) {
	// Sufficiient balance to send an email
	$this->thing->email->sendGeneric($from, "uuid", $subject, $message, $choices);
	$this->thing->account['thing']->Credit(100);
}

*/


        // Make image
		$this->PNG();

        // Make SMS
        $this->makeSMS();
        $this->thing_report['sms'] = $this->sms_message;


        $this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail();
        $this->thing_report['email'] = array('to'=>$this->from,
                'from'=>'channel',
                'subject' => $this->subject,
                'message' => $this->email_message,
                'choices' => false);
 //       require_once '/var/www/html/stackr.ca/agents/makeemail.php';
 //       $email_agent = new Makeemail($this->thing);
 //       $this->thing_report['email'] = $email_agent->thing_report['email'];


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
    //    $this->thing_report['info'] = $message_thing->thing_report['info'] ;

	//	$this->thing_report['thing'] = $this->thing->thing;

            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }
        return $this->thing_report;

	}

    function getMessenger() 
    {
        $this->channel_name = "messenger";
        $this->plain_text_statement .= "Public plain text at some point";

        $this->retention_policy = "private potentially forever";
        $this->privacy_expectation = "key access";

        $this->key .= " | key " . $this->uuid . " ";
        $this->reach = "X-X";
        $this->fields = "Z";
        $this->eyes = 2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";

        $this->images = "PNG";

        $this->ux_ui = "cue based";

    }

    function getEmail() {

        $this->channel_name = "email";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "none";

        $this->reach = "X-X";
        $this->fields = 2;
        $this->eyes =  2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "PNG";

        $this->emoji = "yes";

        $this->attachments = "yes";

        $this->voice = "file attachment";
        $this->video = "file attachment";

        $this->presence = "none";

        $this->ux_ui = "client based";

    }

    function getGmail() {

        $this->channel_name = "gmail";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "none";

        $this->reach = "1-X";
        $this->fields = 2;
        $this->eyes =  2;

        $this->latency = "minutes";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "PNG";

        $this->emoji = "not reviewed";

        $this->attachments = "yes";

        $this->voice = "file attachment";
        $this->video = "file attachment";

        $this->presence = "none";

    }

    function getGooglecalendar()
    {
        $this->channel_name = "google calender";
        $this->plain_text_statement = "encrypted screen delivery";
        $this->retention_policy = "private potentially forever";

        $this->privacy_expectation = "key access";

        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes =  2;

        $this->latency = "seconds";
        $this->characters = "unicode";
        $this->threading = "no";
        $this->images = "no";

        $this->emoji = "yes";

        $this->attachments = "awkward";

        $this->voice = "not reviewed";
        $this->video = "not reviewed";

        $this->presence = "none";
        $this->ux_ui = "not reviewed";
    }

    function blankX() 
    {
        $variables = array("channel_name",
                    "plain_text_statement",
                    "retention_policy",
                    "reach",
                    "fields",
                    "eyes",
                    "latency",
                    "characters",
                    "threading",
                    "association",
                    "cueing",
                    "emoji",
                    "images",
                    "buttons",
                    "carousel",
                    "attachments",
                    "voice",
                    "video",
                    "presence");

        foreach ($variables as $key=>$variable) {

            if (!isset($this->$variable)) {
                $this->$variable = "X";
            }

        }

    }

    function getSlack() {

        $this->channel_name = "slack";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private forever";
        $this->privacy_expectation = "none";
        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes = "Z";

        $this->association = "3rd party"; 

        $this->emoji = "yes";


        $this->latency = "seconds";
        $this->characters = "alphanumber";
        $this->threading = "no";
        $this->images = "PNG";
        $this->voice = "not typically used";
        $this->video = "not typically used";

        $this->presence = "sensitive";


        $this->ux_ui = "not reviewed";



    }

    function getMordok() {

        $this->channel_name .= "mordok";
        $this->plain_text_statement .= "Public plain text at some point";
        $this->retention_policy = "emphemeral days";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes =  "X";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "yes";
        $this->cueing = "yes";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "yes";

        $this->carousel = "no";

        $this->attachments = "no";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "cue based";

    }

    function getSMS() {

        $this->channel_name = "SMS";
        $this->plain_text_statement = "Public plain text";
        $this->retention_policy = "private indefinite";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes =  "X";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "no";
        $this->cueing = "no";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "no";

        $this->carousel = "no";

        $this->attachments = "MMS";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "client based";
    }


    function getConsole() {

        $this->channel_name = "console";
        $this->plain_text_statement = "Public plain text";
        $this->retention_policy = "private indefinite";

        $this->reach = "1-1";
        $this->fields = 2;
        $this->eyes =  "1";

        $this->latency = "seconds";
        $this->characters = "alphanumeric";
        $this->threading = "no";

        $this->association = "no";
        $this->cueing = "no";

        $this->emoji = "yes";
        $this->images = "PNG";
        $this->buttons = "no";

        $this->carousel = "no";

        $this->attachments = "MMS";
        $this->voice = "no";
        $this->video = "no";

        $this->presence = "none";
        $this->ux_ui = "client based";
    }


    function getSlack2() {

        $this->channel_name = "slack";
        $this->plain_text_statement = "Public plain text at some point";
        $this->retention_policy = "private forever";
        $this->privacy_expectation = "none";
        $this->reach = "1-X";
        $this->fields = "Z";
        $this->eyes = "Z";

        $this->latency = "seconds";
        $this->characters = "alphanumber";
        $this->threading = "no";
        $this->images = "PNG";

        $this->voice = "not typically used";
        $this->video = "not typically used";

        $this->presence = "slow";

        $this->ux_ui = "slow app start-up";

    }


    function readFrom()
    {

        if (isset($this->channel_name)) {return;}

        if (strlen($this->from) == 16 and (is_numeric($this->from))) { 
            //$this->channel = "messenger";
            $this->getMessenger();
            return;
        }

        if ( filter_var($this->from, FILTER_VALIDATE_EMAIL) ) {
            //$this->channel = "email";
            $this->getEmail();
            return;
        }


        if (strlen($this->from) == 11 and (is_numeric($this->from))) { 
            // Comes in as 11.  Perhaps has a blank space.
            //$this->channel = "SMS";
            $this->getSMS();
            return; 
       }

        if ($this->from == "console") { 
            //$this->channel = "console";
            $this->getConsole();
            return;
        }


        $this->channel = "unknown";
        return;

    }

	public function readSubject()
    {
		$status = true;
	    return $status;
	}

    public function PNG()
    {
        $this->thing_report['png'] = null;
        return $this->thing_report['png'];
    }


    function makeSMS () {


        $sms_verbosity_levels = array("channel_name"=>1,
                    "plain_text_statement"=>2,
                    "retention_policy"=>2,
                    "reach"=>5,
                    "fields"=>5,
                    "eyes"=>9,
                    "latency"=>6,
                    "characters"=>4,
                    "threading"=>4,
                    "association"=>3,
                    "cueing"=>2,
                    "emoji"=>2,
                    "images"=>7,
                    "buttons"=>7,
                    "carousel"=>7,
                    "attachments"=>8,
                    "voice"=>8,
                    "video"=>8,
                    "presence"=>2); 


        $this->verbosity = 9;

        $this->sms_message = "CHANNEL ";
        $this->sms_message .= " | " . $this->channel_name;
        $this->sms_message .= " | " . $this->plain_text_statement;
        $this->sms_message .= " | " . $this->retention_policy;

        $this->sms_message .= " | reach ". $this->reach;
        $this->sms_message .= " fields " . $this->fields;
        $this->sms_message .= " eyes " . $this->eyes;

        $this->sms_message .= " | latency " . $this->latency;
        $this->sms_message .= " characters " . $this->characters;
        $this->sms_message .= " threading " .$this->threading;

        $this->sms_message .= " | assocation " . $this->association;
        $this->sms_message .= " cueing " . $this->cueing;

        $this->sms_message .= " | emoji " . $this->emoji;
        $this->sms_message .= " images " . $this->images;
        $this->sms_message .= " buttons " . $this->buttons;

        $this->sms_message .= " | carousel " .$this->carousel;

        $this->sms_message .= " attachments " . $this->attachments;
        $this->sms_message .= " voice " . $this->voice;
        $this->sms_message .= " video " . $this->video;

        $this->sms_message .= " presence " .$this->presence;

        $run_time = microtime(true) - $this->start_time;
        $milliseconds = round($run_time * 1000);
        $this->sms_message .= " | ~rtime " .  number_format($milliseconds) . "ms";

        $this->sms_message .= " | TEXT " . strtoupper($this->channel_name);

    }


    function makeEmail()
    {
        if (!isset($this->sms_message)) {$this->makeSMS();}
        $this->email_message = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

    }

}






?>
