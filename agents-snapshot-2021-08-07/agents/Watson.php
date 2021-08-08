<?php
namespace Nrwtaylor\StackAgentThing;

class Watson
{
	public $var = 'hello';
    // https://www.wired.com/2011/03/0310bell-invents-telephone-mr-watson-come-here/
    // Mr. Watson – come here – I want to see you.

    function __construct(Thing $thing)
    {
		$this->thing = $thing;

        $this->api_key = $this->thing->container['api']['watson'];
        $this->thing_report['thing'] = $this->thing->thing;

		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
    	$this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( 'Agent "Watson" running on Thing ' . $this->thing->nuuid . '' );
		$this->thing->log ( 'Agent "Watson" received this Thing "' .  $this->subject .  '"' );

		$this->readSubject();
		$this->respond();

		$this->thing->log( 'Agent "Watson" complete' );

		return;
    }





// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "watson";

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = "Watson says hello";

		$this->sms_message = "WATSON | Says hello. | REPLY QUESTION";
		$this->message = "Watson says hello";
		$this->keyword = "watson";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
                $this->thing_report['message'] = $this->message;
		$this->thing_report['email'] = $this->message;

		return $this->response;
	}
}




return;
