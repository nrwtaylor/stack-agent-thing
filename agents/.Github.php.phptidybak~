<?php
namespace Nrwtaylor\StackAgentThing;

class Github {
    // Not associated with Github.
    // Except the stack-agent-thing package is shared there.
    // But a Thing needs to know what Github is.

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
		$this->thing = $thing;
        $this->thing_report['thing'] = $thing;
        $this->agent_input = $agent_input;

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
		$from = "github";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = "Provided the stack-agent-thing github location.";

		$this->sms_message = "GITHUB | https://github.com/nrwtaylor/stack-agent-thing | REPLY QUESTION";
		$this->message = "www.github.com";
		$this->keyword = "github";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;

		return;
	}
}

?>
