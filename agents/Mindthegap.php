<?php
namespace Nrwtaylor\StackAgentThing;

class Mindthegap {
    // Not associated with Transport London.
    // But a Thing needs to know what minding the gap is.

	public $var = 'hello';


    function __construct(Thing $thing)
    {
		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;
        $this->agent_name = "mindthegap";

		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
      	$this->to = $thing->to;
       	$this->from = $thing->from;
       	$this->subject = $thing->subject;
		$this->sqlresponse = null;

		$this->thing->log( 'Agent "Mind The Gap" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log( 'Agent "Mind The Gap" received this Thing "' . $this->subject . '"');

		//echo "construct email responser";

		$this->readSubject();
		$this->respond();

		$this->thing->log( 'Agent "Mind The Gap" completed.');

		return;
    }





// -----------------------

	private function respond() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "mindthegap";

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;


	}

	public function readSubject() {

		$this->response = "Mind the gap.";

		$this->sms_message = "MIND THE GAP | https://www.youtube.com/watch?v=_WpC2pnaAWI";
		$this->message = "https://www.youtube.com/WpC2pnaAWI";
		$this->keyword = "mind";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;

		return $this->response;
	}
}

?>

