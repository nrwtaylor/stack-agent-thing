<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Whatis {

	public $var = 'hello';

    function __construct(Thing $thing)
    {
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;

		$this->agent_name = 'whatis';

		// Stack development is iterative.
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        // Get the uuid, the agent to the datagram is addressed to, the
        // address from which the datagram came from, and
        // the subject line.
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // No underlying database check
		$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		// Allow for a new state tree to be introduced here.
		$this->node_list = array("what is"=>array("channel", "agent"));

		$this->thing->log('Agent "Whatis" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Whatis" received this Thing "' . $this->subject .  '".');

		$this->readSubject();
		$this->thing_report = $this->respond();

        $this->privacy();

		$this->thing->log('Agent "Whatis" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . "ms.");

		return;
    }

    public function privacy()
    {
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
       	$this->sms_message = "WHATIS | This is (778) 401-2132, the BC SMS portal to Stackr. | " . $this->web_prefix . "" ;
        $this->thing_report['sms'] = $this->sms_message;
        $this->message = $this->sms_message;
		return $this->message;
	}

    public function makeEmail()
    {
        // devstack review Thing link in email for limitedbeta
        $message = "This is " . str_replace("@","", $this->mail_post_fix) . ", the limited beta service portal to " . ucwords($this->word) . ".\r\n";
        $message .= "Only the subject line, any " . $this->mail_postfix . " email addresses in the address and the return email address is processed by " . ucwords($this->word) . ".\r\n";

        $message .= 'For a full statement of our privacy policy, please goto to <a href="' . $this->web_prefix . 'privacy">' . $this->web_prefix . 'privacy</a>';

        $this->thing_report['email'] = $message;

        return;
    }

    public function makePDF()
    {

        // We'll be outputting a PDF
        //header('Content-type: application/pdf');

        // It will be called downloaded.pdf
        //header('Content-Disposition: attachment; filename="whatis.pdf"');

        // The PDF source is in original.pdf
        $image = file_get_contents($GLOBALS['stack_path'] . '/resources/whatis/whatis.pdf');
       

                //ob_start();
                //$image = $pdf->Output('', 'I');
                //$image = ob_get_contents();
                //ob_clean();
        //return;

//http://www.fpdf.org/en/script/script45.php

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
		$from = "whatis";

        // Make some buttons
        $this->makeChoices();

        // Send a response back (subject to "Message" Agent).
        // And report on messaging.
        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        // Package the other messages up
        $this->makeTxt();
        $this->makeMessage();

        $this->makePDF();

        // Offer help
		$this->thing_report['help'] = 'This is the Whatis responder. It has a go at figuring out what a Thing is. Email whatis' . $this->mail_post_fix . '.';

		return $this->thing_report;
	}

	public function readSubject()
    {
        // Interpret request as ...
        $this->thing_report['request'] = "What is?";
        // And a little more
		$this->thing_report['message'] = "Request to know what something is.";

		return "Message not understood";
	}

}




?>



