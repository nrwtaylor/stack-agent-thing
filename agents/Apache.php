<?php
namespace Nrwtaylor\StackAgentThing;

class Apache
{
	public $var = 'hello';

    function __construct(Thing $thing)
    {
        // Uncomment to provide phpinfo report
        phpinfo();


		$this->thing = $thing;

//        $this->api_key = $this->thing->container['api']['watson'];
        $this->thing_report['thing'] = $this->thing->thing;

		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
    	$this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( 'Agent "Apache" running on Thing ' . $this->thing->nuuid . '' );
		$this->thing->log ( 'Agent "Apache" received this Thing "' .  $this->subject .  '"' );

		$this->readSubject();
		$this->respond();

		$this->thing->log( 'Agent "Apache" complete' );

		return;
    }





// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "apache";

        $message_thing = new Message($this->thing, $this->thing_report);
		$this->thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = "Apache says hello";

		$this->sms_message = "APACHE | Says hello | REPLY QUESTION";
		$this->message = "Apache says hello";
		$this->keyword = "apache";

		$this->thing_report['keyword'] = $this->keyword;
		$this->thing_report['sms'] = $this->sms_message;
                $this->thing_report['message'] = $this->message;
		$this->thing_report['email'] = $this->message;

		return $this->response;
	}
}




return;
