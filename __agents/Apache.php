<?php
namespace Nrwtaylor\StackAgentThing;

class Apache extends Agent
{
	public $var = 'hello';
    function init()
    {
		$this->retain_for = 24; // Retain for at least 24 hours.
    }

    private function getApache()
    {
         phpinfo();
    }

    public function test()
    {


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
