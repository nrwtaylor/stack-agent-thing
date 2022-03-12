<?php
namespace Nrwtaylor\StackAgentThing;

class Secondnarrows extends Agent
{
    public $var = "hello";

    public function init()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $this->thing_report["help"] =
            "This agent provides a link to tides and currents at Second Narrows.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
        $this->response .= "Made a link.";

        // Second narrows
        // http://www.waterlevels.gc.ca/eng/data/table/2018/curr_ref/4100

        $this->sms_message =
            "Second Narrows | https://tides.mobilegeographics.com/locations/7266.html | Provided a link.";

        $this->message =
            "http://tides.mobilegeographics.com/locations/7266.html";
        $this->keyword = "tide";

        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

    }
}
