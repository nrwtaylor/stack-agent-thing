<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Privacy {


	public $var = 'hello';


    function __construct(Thing $thing) {

		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;

		$this->agent_name = 'privacy';

		// Stack development is iterative.
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        // Get the uuid, the agent to the datagram is addressed to, the 
        // address from which the datagram came from, and
        // the subject line.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("start", "opt-in"));

		$this->thing->log('Agent "Privacy" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Privacy" received this Thing "' . $this->subject .  '".');



		$this->readSubject();
		$this->thing_report = $this->respond();

        $this->privacy();

		$this->thing->log('Agent "Privacy" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . "ms.");

		return;

		}

    public function privacy()
    {
    }


    public function makeWeb()
    {
        $file = $GLOBALS['stack_path'] . 'resources/privacy/privacy.html';
        $contents = file_get_contents($file);
        $this->thing_report['web'] = $contents;
    }

	public function makeSMS()
    {
       	$this->sms_message = "PRIVACY | Records of the subject/chat, originating address and destination agent are retained until they are forgotten.  Records may be forgotten at anytime either by the system or by the Identity. Forgetall will forget all of an Identity's Things. Things may contain nominal key accesible information. | " . $this->web_prefix ."privacy" ;
        $this->thing_report['sms'] = $this->sms_message;
        $this->message = $this->sms_message;
		return $this->message;
	}

    public function makeEmail()
    {
        $message = "Thank you. Privacy is really important to " . ucwords($this->word) . ". Records deposited with " . ucwords($this->word) . " may be forgotten at any time.\r\n";
        $message .= "The address fields (to:, cc:, and bcc:) are stripped of non-Stackr emails, and the subject line is processed by " . ucwords($this->word) . ".\r\n";
        $message .= "An instruction to Stackr to remove all message records associated with this email address can be sent to forgetall" . $this->mail_postfix . ".\r\n";

        $message .= 'For a full statement of our privacy policy, please goto to <a href="' . $this->web_prefix . ' privacy">' . $this->web_prefix . 'privacy</a>';

        $this->thing_report['email'] = $message;

        return;
    }

    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
        return;
    }

	private function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

        $this->makeSMS();
        $this->makeWeb();
        $this->makeEmail();

		$to = $this->thing->from;
		$from = "privacy";

        $this->makeChoices();
 //       // Make buttons
//		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
//		$choices = $this->thing->choice->makeLinks('start');
  //      // $choices = false;
	//	$this->thing_report['choices'] = $choices;


        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

		$this->thing_report['help'] = 'This is the Privacy manager. Email privacy' . $this->mail_postfix .'.';

		return $this->thing_report;
	}

	public function readSubject()
    {

        $this->thing_report['request'] = "What is Privacy?";

		$this->thing_report['message'] = "Request for web privacy policy.";

		return "Message not understood";
	}



}




?>



