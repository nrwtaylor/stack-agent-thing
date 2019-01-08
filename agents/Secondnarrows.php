<?php
namespace Nrwtaylor\StackAgentThing;

class Secondnarrows {
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
		$from = "second narrows";

        $this->thing_report['help'] = "This agent provides a link to tides and currents at Second Narrows.";

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = "Made a link.";

		// $this->sms_message = "Second Narrows | authorative http://www.tides.gc.ca/eng/data/table/2018/curr_ref/4100";

        // Second narrows
        // http://www.waterlevels.gc.ca/eng/data/table/2018/curr_ref/4100

        $this->sms_message = "Second Narrows | http://tides.mobilegeographics.com/locations/5715.html";

		$this->message = "http://tides.mobilegeographics.com/locations/5715.html";
		$this->keyword = "tide";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

		return $this->response;
	}
}

?>
