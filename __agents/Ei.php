<?php
namespace Nrwtaylor\StackAgentThing;

class Ei {
    // Not associated with the Government of Canada.
    // But a Thing needs to know what Employment Insurance is.

	public $var = 'hello';


    function __construct(Thing $thing)
    {
		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;

		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
      	$this->to = $thing->to;
       	$this->from = $thing->from;
       	$this->subject = $thing->subject;
		$this->sqlresponse = null;

		$this->thing->log( 'running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log( 'received this Thing "' . $this->subject . '"');

		$this->readSubject();
		$this->respond();

		$this->thing->log( 'completed.');

        $this->thing_report['response'] = $this->response;

		return;
    }

// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "ei";

        $this->thing_report['help'] = "This agent provides a link to the on-line form to complete for Employment Insurance in Canada.";

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = "Made a link.";

		$this->sms_message = "EI (Employment Insurance) | https://www.canada.ca/en/services/benefits/ei/ei-apply-online.html | Link to apply for Employment Insurance online";
		$this->message = "https://www.canada.ca/en/services/benefits/ei/ei-apply-online.html";
		$this->keyword = "employment";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

		return $this->response;
	}
}

?>
