<?php
namespace Nrwtaylor\StackAgentThing;

class Watson extends Agent
{
    public $var = "hello";
    // https://www.wired.com/2011/03/0310bell-invents-telephone-mr-watson-come-here/
    // Mr. Watson – come here – I want to see you.

    public function init()
    {
        $this->api_key = $this->thing->container["api"]["watson"];
        $this->thing_report["thing"] = $this->thing->thing;

        $this->retain_for = 24; // Retain for at least 24 hours.
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
        $this->response .= "Watson says hello";

        $this->sms_message = "WATSON | Says hello. | REPLY QUESTION";
        $this->message = "Watson says hello";
        $this->keyword = "watson";

        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["message"] = $this->message;
        $this->thing_report["email"] = $this->message;
    }
}
