<?php
namespace Nrwtaylor\StackAgentThing;

class Firstnarrows extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["help"] =
            "This agent provides a link to tides and currents at First Narrows.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
        // Point Atkinson
        // http://www.waterlevels.gc.ca/eng/station?type=0&date=2018%2F10%2F18&sid=7795&tz=PDT&pres=2

        // First Narrows
        // Current
        // http://www.waterlevels.gc.ca/eng/data/table/2018/curr_ref/4100

        $this->response .= "Made a link. ";

        // $this->sms_message = "Second Narrows | authorative http://www.tides.gc.ca/eng/data/table/2018/curr_ref/4100";
        //$this->sms_message = "First Narrows | http://tides.mobilegeographics.com/locations/1921.html";
        $this->sms_message =
            "First Narrows | https://tides.mobilegeographics.com/locations/2504.html | Provided a link.";
        $this->message =
            "http://tides.mobilegeographics.com/locations/2504.html";
        $this->keyword = "tide";

        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;
    }
}
