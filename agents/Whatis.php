<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Whatis extends Agent {

	public $var = 'hello';

    public function init()
    {
		// Stack development is iterative.
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("what is"=>array("channel", "agent"));
    }

    public function run() {
    }

    public function makeWeb()
    {
        $file = $GLOBALS['stack_path'] . '/resources/whatis/web.html';
        $contents = file_get_contents($file);

        $url = $this->web_prefix . 'thing/'.$this->uuid .'/whatis.pdf';

        $contents .= "<p>Here is a PDF with more whatis.";
        $contents .= "<br><a href='" . $url . "'>" . $url . "</a>";
        $this->thing_report['web'] = $contents;
    }

    public function makeMessage()
    {
        $file = $GLOBALS['stack_path'] . '/resources/whatis/message.txt';
        $contents = file_get_contents($file);
        $this->thing_report['message'] = $contents;
    }

    public function makeTxt()
    {
        $file = $GLOBALS['stack_path'] . '/resources/whatis/message.txt';
        $contents = file_get_contents($file);

        if (!isset($this->thing_report['web'])) {
            $this->makeWeb();
        }

        $text = $this->thing_report['web'];

        strip_tags(str_replace(array("<b>", "</b>"), array("", ""), $text));

        strip_tags(str_replace(array("<br>", "<p>"), array("\n", "\n"), $text));


        $this->thing_report['txt'] = $text;
    }

	public function makeSMS()
    {
       	$this->sms_message = "WHATIS | This is (778) 401-2132, the BC SMS portal to Stackr. | " . $this->web_prefix . "" . " | TEXT HELP" ;
        $this->thing_report['sms'] = $this->sms_message;
        $this->message = $this->sms_message;
	}

    public function makeEmail()
    {
        // devstack review Thing link in email for limitedbeta
        $message = "This is " . str_replace("@","", $this->mail_postfix) . ", the limited beta service portal to " . ucwords($this->word) . ".\r\n";
        $message .= "Only the subject line, any " . $this->mail_postfix . " email addresses in the address and the return email address is processed by " . ucwords($this->word) . ".\r\n";

        $message .= 'For a full statement of our privacy policy, please goto to <a href="' . $this->web_prefix . 'privacy">' . $this->web_prefix . 'privacy</a>';

        $this->thing_report['email'] = $message;
    }

    public function makePDF()
    {
        $file = $GLOBALS['stack_path'] . '/resources/whatis/whatis.pdf';
        if (!file_exists($file)) {
            $this->thing_report['pdf'] = null;
            return $this->thing_report['pdf'];
        }
        // The PDF source is in original.pdf
        $image = file_get_contents($file);

        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }

    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "what is");
        $choices = $this->thing->choice->makeLinks('what is');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
    }

	public function respondResponse()
    {
		// Thing actions
		$this->thing->flagGreen();

        // Make some buttons
        $this->makeChoices();

        // Send a response back (subject to "Message" Agent).
        // And report on messaging.
        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makePDF();

        // Offer help
		$this->thing_report['help'] = 'This is the Whatis responder. It has a go at figuring out what a Thing is. Email whatis' . $this->mail_postfix . '.';

	}

	public function readSubject()
    {
        // Interpret request as ...
        $this->thing_report['request'] = "What is?";
        // And a little more
		$this->thing_report['message'] = "Request to know what something is.";

	}

}
