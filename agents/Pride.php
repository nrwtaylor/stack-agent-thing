<?php
namespace Nrwtaylor\StackAgentThing;
//echo "Watson says hi<br>";

class Pride {
    // Not associated with Google.
    // But a Thing needs to know what Google is.

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
		$this->thing = $thing;

        $this->thing_report['thing'] = $this->thing->thing;


		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
      	$this->to = $thing->to;
       	$this->from = $thing->from;
       	$this->subject = $thing->subject;
		$this->sqlresponse = null;

		$this->thing->log( 'Agent "Pride" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log( 'Agent "Pride" received this Thing "' . $this->subject . '"');

		//echo "construct email responser";

		$this->readSubject();
		$this->respond();

		$this->thing->log( 'Agent "Pride" completed.');

		return;

		}





// -----------------------

	private function respond() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "google";
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }
		return $this->thing_report;


	}



	public function readSubject() {

		$this->response = "Watson says hello";

		$this->sms_message = "PRIDE VANCOUVER | http://www.vancouverpride.ca/ | TEXT PRIDE SNOWFLAKE";
		$this->message = "http://www.vancouverpride.ca/";
		$this->keyword = "pride";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;

		return $this->response;
	}
}
