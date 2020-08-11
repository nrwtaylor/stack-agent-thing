<?php
namespace Nrwtaylor\StackAgentThing;

class Google extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->test = "Development code";

        $this->retain_for = 24; // Retain for at least 24 hours.
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function readSubject()
    {
        //mail("nick@wildnomad.com","watson.php readSubject() run" ,"Test message");
        //echo "Hello";
        $this->response = "Watson says hello";

        $this->sms_message = "GOOGLE | https://google.com | REPLY QUESTION";
        $this->message = "https://google.com";
        $this->keyword = "google";

        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;

        //		return $this->response;
    }
}
