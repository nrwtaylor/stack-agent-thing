<?php
namespace Nrwtaylor\StackAgentThing;

class Ei extends Agent
{
    // Not associated with the Government of Canada.
    // But a Thing needs to know what Employment Insurance is.

    public $var = "hello";

    public function init()
    {
        $this->retain_for = 24; // Retain for at least 24 hours.
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["help"] =
            "This agent provides a link to the on-line form to complete for Employment Insurance in Canada.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
        $this->response .= "Made a link.";

        $this->sms_message =
            "EI (Employment Insurance) | https://www.canada.ca/en/services/benefits/ei/ei-apply-online.html | Link to apply for Employment Insurance online";
        $this->message =
            "https://www.canada.ca/en/services/benefits/ei/ei-apply-online.html";
        $this->keyword = "employment";

        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;
    }
}
