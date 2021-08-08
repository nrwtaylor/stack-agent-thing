<?php
namespace Nrwtaylor\StackAgentThing;

class Mindthegap extends Agent {
    // Not associated with Transport London.
    // But a Thing needs to know what minding the gap is.

	public $var = 'hello';

    function init()
    {
		$this->retain_for = 24; // Retain for at least 24 hours.
    }

	public function readSubject()
    {
		$this->response = "Mind the gap.";

		$this->sms_message = "MIND THE GAP | https://www.youtube.com/watch?v=_WpC2pnaAWI | TEXT TRANSIT";
		$this->message = "https://www.youtube.com/WpC2pnaAWI";
		$this->keyword = "mind";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;
	}

}


